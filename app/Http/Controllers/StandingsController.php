<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\View\View;

class StandingsController extends Controller
{
    public function index(): View
    {
        return view('standings.index');
    }

    public function adminIndex(): View
    {
        $sports = Sport::orderBy('order')->with('categories')->get();
        $teams = Team::orderBy('name')->get()->keyBy('id');

        // Step 1: Compute per-category standings using the same logic as scores/standings pages
        // computeStandings() returns a numerically-indexed array (usort resets keys),
        // each element: ['team' => Team, 'played' => int, 'won' => int, ..., 'points' => int]
        // Points use: Win = 3, Draw = 1, Loss = 0 (places/racing use separate scale)
        $categoryStandings = [];
        foreach ($sports as $sport) {
            foreach ($sport->categories as $category) {
                $games = $category->games()
                    ->select(['id', 'category_id', 'team_home_id', 'team_away_id', 'score_home', 'score_away', 'game_data', 'status', 'winner_id', 'disqualified_team'])
                    ->where('status', 'completed')
                    ->get();
                $categoryStandings[$category->id] = $this->computeStandings($games, $teams);
            }
        }

        // Step 2: Aggregate per-sport standings (sum PTS from all categories of each sport)
        $sportStandings = [];
        foreach ($sports as $sport) {
            $sportPoints = [];

            foreach ($sport->categories as $category) {
                foreach ($categoryStandings[$category->id] ?? [] as $row) {
                    $teamId = $row['team']->id;

                    // Skip teams that didn't play any games in this category
                    if ($row['played'] === 0) {
                        continue;
                    }

                    if (! isset($sportPoints[$teamId])) {
                        $sportPoints[$teamId] = [
                            'team' => $row['team'],
                            'points' => 0,
                            'goal_difference' => 0,
                        ];
                    }

                    $sportPoints[$teamId]['points'] += $row['points'];
                    $sportPoints[$teamId]['goal_difference'] += $row['goal_difference'];
                }
            }

            // Sort by points descending
            uasort($sportPoints, fn ($a, $b) => $b['points'] <=> $a['points']);

            $sportStandings[$sport->slug] = [
                'sport' => $sport,
                'standings' => $sportPoints,
            ];
        }

        // Step 3: Aggregate overall standings (sum PTS from all categories across all sports)
        $overallPoints = [];
        foreach ($sportStandings as $sportData) {
            foreach ($sportData['standings'] as $teamId => $row) {
                if (! isset($overallPoints[$teamId])) {
                    $overallPoints[$teamId] = [
                        'team' => $row['team'],
                        'points' => 0,
                        'goal_difference' => 0,
                    ];
                }
                $overallPoints[$teamId]['points'] += $row['points'];
                $overallPoints[$teamId]['goal_difference'] += $row['goal_difference'];
            }
        }

        // Sort by points descending
        uasort($overallPoints, fn ($a, $b) => $b['points'] <=> $a['points']);

        return view('admin.standings', compact('sports', 'sportStandings', 'overallPoints'));
    }

    public function adminShow(Sport $sport, Category $category): View
    {
        abort_unless($category->sport_id === $sport->id, 404);

        $games = cache()->remember('standings_games_'.$category->id, 30, function () use ($category) {
            return $category->games()
                ->select([
                    'id',
                    'category_id',
                    'team_home_id',
                    'team_away_id',
                    'score_home',
                    'score_away',
                    'game_data',
                    'status',
                    'winner_id',
                    'disqualified_team',
                ])
                ->with(['teamHome', 'teamAway', 'winner'])
                ->orderBy('match_number')
                ->get();
        });

        $sports = cache()->remember('standings_sports_ordered', 600, function () {
            return Sport::orderBy('order')->get();
        });

        $teams = cache()->remember('teams_by_id', 600, function () {
            return Team::orderBy('name')->get()->keyBy('id');
        });
        $standings = $this->computeStandings($games, $teams);

        $totalMatches = $games->flatMap(function ($game) {
            return [$game->team_home_id, $game->team_away_id];
        })->unique()->count() - 1;

        return view('admin.standings.show', compact('sport', 'category', 'games', 'sports', 'standings', 'totalMatches'));
    }

    public function show(Sport $sport, Category $category): View
    {
        abort_unless($category->sport_id === $sport->id, 404);

        $games = cache()->remember('standings_games_'.$category->id, 30, function () use ($category) {
            return $category->games()
                ->select([
                    'id',
                    'category_id',
                    'team_home_id',
                    'team_away_id',
                    'score_home',
                    'score_away',
                    'game_data',
                    'status',
                    'winner_id',
                    'disqualified_team',
                ])
                ->with(['teamHome', 'teamAway', 'winner'])
                ->orderBy('match_number')
                ->get();
        });

        $sports = cache()->remember('standings_sports_ordered', 600, function () {
            return Sport::orderBy('order')->get();
        });

        $teams = cache()->remember('teams_by_id', 600, function () {
            return Team::orderBy('name')->get()->keyBy('id');
        });
        $standings = $this->computeStandings($games, $teams);

        $totalMatches = $games->flatMap(function ($game) {
            return [$game->team_home_id, $game->team_away_id];
        })->unique()->count() - 1;

        return view('standings.show', compact('sport', 'category', 'games', 'sports', 'standings', 'totalMatches'));
    }

    private function getPlacePoints(int $place): int
    {
        return match ($place) {
            1 => 4,
            2 => 3,
            3 => 2,
            4 => 1,
            default => 0,
        };
    }

    /**
     * Compute round-robin standings from games.
     * Points: Win = 3, Draw = 1, Loss = 0
     * For places type (racing): 1st=4, 2nd=3, 3rd=2, 4th=1
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
            if (! $game->isCompleted()) {
                continue;
            }

            // Handle places/racing type games
            $gameData = $game->game_data ?? [];
            $places = $gameData['places'] ?? [];

            if (! empty($places) && is_array($places)) {
                foreach ($places as $place => $teamId) {
                    if ($teamId && isset($stats[$teamId])) {
                        $stats[$teamId]['played']++;
                        $stats[$teamId]['points'] += $this->getPlacePoints((int) $place);
                    }
                }

                continue;
            }

            $homeId = $game->team_home_id;
            $awayId = $game->team_away_id;
            $scoreHome = $game->score_home ?? 0;
            $scoreAway = $game->score_away ?? 0;

            // Handle disqualification
            if ($game->disqualified_team) {
                $stats[$homeId]['played']++;
                $stats[$awayId]['played']++;

                if ($game->disqualified_team === 'home') {
                    $stats[$awayId]['won']++;
                    $stats[$awayId]['points'] += 3;
                    $stats[$homeId]['lost']++;
                } elseif ($game->disqualified_team === 'away') {
                    $stats[$homeId]['won']++;
                    $stats[$homeId]['points'] += 3;
                    $stats[$awayId]['lost']++;
                } else {
                    $stats[$homeId]['lost']++;
                    $stats[$awayId]['lost']++;
                }

                continue;
            }

            // Both teams played
            $stats[$homeId]['played']++;
            $stats[$awayId]['played']++;

            // Goals
            $stats[$homeId]['goals_for'] += $scoreHome;
            $stats[$homeId]['goals_against'] += $scoreAway;
            $stats[$awayId]['goals_for'] += $scoreAway;
            $stats[$awayId]['goals_against'] += $scoreHome;

            // Win/Draw/Loss
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

        // Compute goal difference
        foreach ($stats as &$s) {
            $s['goal_difference'] = $s['goals_for'] - $s['goals_against'];
        }
        unset($s);

        // Sort: points desc, goal_difference desc, goals_for desc
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
