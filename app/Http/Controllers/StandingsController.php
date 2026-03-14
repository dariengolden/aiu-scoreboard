<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\View\View;

class StandingsController extends Controller
{
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

        return view('standings.show', compact('sport', 'category', 'games', 'sports', 'standings'));
    }

    private function getPlacePoints(int $place): int
    {
        return match ($place) {
            1 => 10,
            2 => 8,
            3 => 6,
            4 => 4,
            default => 0,
        };
    }

    /**
     * Compute round-robin standings from games.
     * Points: Win = 3, Draw = 1, Loss = 0
     * For places type (racing): 1st=10, 2nd=8, 3rd=6, 4th=4
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
