<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportController extends Controller
{
    public function index(): View
    {
        $sports = cache()->remember('scores_sports_with_categories', 600, function () {
            return Sport::orderBy('order')->with('categories')->where('slug', '!=', 'events')->get();
        });

        return view('scores.index', compact('sports'));
    }

    public function show(Request $request, Sport $sport): View
    {
        if ($sport->slug === 'events') {
            abort(404);
        }

        $selectedCategory = $request->query('category');

        $categories = $sport->categories()->get();

        if (! $selectedCategory) {
            $menCategory = $categories->first(fn ($c) => stripos($c->name, 'Men') !== false);
            $selectedCategory = $menCategory?->slug;
        }

        $cacheKey = 'scores_games_'.$sport->id.'_'.($selectedCategory ?: 'all');

        $games = cache()->remember($cacheKey, 30, function () use ($sport, $selectedCategory) {
            $gamesQuery = Game::select([
                    'id',
                    'category_id',
                    'team_home_id',
                    'team_away_id',
                    'score_home',
                    'score_away',
                    'status',
                    'winner_id',
                    'match_number',
                ])
                ->with(['teamHome', 'teamAway', 'winner', 'category'])
                ->whereNull('event_type')
                ->whereHas('category', fn ($q) => $q->where('sport_id', $sport->id))
                ->orderBy('match_number');

            if ($selectedCategory) {
                $gamesQuery->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
            }

            return $gamesQuery->get()->groupBy('category_id');
        });

        $visibleCategories = $selectedCategory
            ? $categories->where('slug', $selectedCategory)
            : $categories;

        $teamIds = collect();
        foreach ($visibleCategories as $category) {
            $categoryGames = $games[$category->id] ?? collect();
            foreach ($categoryGames as $game) {
                $teamIds->push($game->team_home_id);
                $teamIds->push($game->team_away_id);
            }
        }
        $teamIds = $teamIds->unique()->filter();
        $teams = $teamIds->isNotEmpty()
            ? Team::whereIn('id', $teamIds)->orderBy('name')->get()->keyBy('id')
            : collect();

        $standingsByCategory = [];
        foreach ($visibleCategories as $category) {
            $categoryGames = $games[$category->id] ?? collect();
            $standingsByCategory[$category->id] = $this->computeStandings($categoryGames, $teams);
        }

        return view('scores.show', compact('sport', 'selectedCategory', 'standingsByCategory', 'visibleCategories', 'games', 'categories'));
    }

    /**
     * Compute round-robin standings from games.
     * Points: Win = 3, Draw = 1, Loss = 0
     */
    private function computeStandings($games, $teams): array
    {
        $stats = [];

        foreach ($teams as $team) {
            $stats[$team->id] = [
                'team' => $team,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        foreach ($games as $game) {
            if ($game->status !== 'completed') {
                continue;
            }

            $homeId = $game->team_home_id;
            $awayId = $game->team_away_id;
            $scoreHome = $game->score_home ?? 0;
            $scoreAway = $game->score_away ?? 0;

            $stats[$homeId]['played']++;
            $stats[$awayId]['played']++;

            $stats[$homeId]['goals_for'] += $scoreHome;
            $stats[$homeId]['goals_against'] += $scoreAway;
            $stats[$awayId]['goals_for'] += $scoreAway;
            $stats[$awayId]['goals_against'] += $scoreHome;

            if ($scoreHome > $scoreAway) {
                $stats[$homeId]['won']++;
                $stats[$homeId]['points'] += 3;
                $stats[$awayId]['lost']++;
            } elseif ($scoreAway > $scoreHome) {
                $stats[$awayId]['won']++;
                $stats[$awayId]['points'] += 3;
                $stats[$homeId]['lost']++;
            } else {
                $stats[$homeId]['drawn']++;
                $stats[$homeId]['points'] += 1;
                $stats[$awayId]['drawn']++;
                $stats[$awayId]['points'] += 1;
            }
        }

        foreach ($stats as &$s) {
            $s['goal_difference'] = $s['goals_for'] - $s['goals_against'];
        }
        unset($s);

        usort($stats, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] - $a['points'];
            }
            if ($a['goal_difference'] !== $b['goal_difference']) {
                return $b['goal_difference'] - $a['goal_difference'];
            }

            return $b['goals_for'] - $a['goals_for'];
        });

        return $stats;
    }
}
