<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $liveGames = cache()->remember('home_live_games', 5, function () {
            $now = now();

            return Game::select([
                'id',
                'category_id',
                'team_home_id',
                'team_away_id',
                'score_home',
                'score_away',
                'game_data',
                'game_format',
                'current_period',
                'status',
                'scheduled_at',
                'scheduled_end_at',
                'location',
                'winner_id',
                'event_type',
                'event_title',
                'match_number',
            ])
                ->where(function ($q) use ($now) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'in_progress')->whereNull('event_type');
                    })->orWhere(function ($q2) use ($now) {
                        $q2->whereNotNull('event_type')
                            ->where('scheduled_at', '<=', $now)
                            ->where('scheduled_end_at', '>=', $now);
                    });
                })
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->orderByRaw('CASE WHEN event_type IS NOT NULL THEN 0 ELSE 1 END')
                ->orderBy('scheduled_at')
                ->get();
        });

        $recentResults = cache()->remember('home_recent_results', 60, function () {
            return Game::select([
                'id',
                'category_id',
                'team_home_id',
                'team_away_id',
                'score_home',
                'score_away',
                'game_data',
                'game_format',
                'current_period',
                'status',
                'scheduled_at',
                'location',
                'winner_id',
                'event_type',
                'event_title',
                'match_number',
            ])
                ->where('status', 'completed')
                ->where('scheduled_at', '>=', now()->subHours(24))
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->orderByDesc('scheduled_at')
                ->limit(6)
                ->get();
        });

        $upcomingGames = cache()->remember('home_upcoming_games', 60, function () {
            return Game::select([
                'id',
                'category_id',
                'team_home_id',
                'team_away_id',
                'score_home',
                'score_away',
                'status',
                'scheduled_at',
                'location',
                'event_type',
                'event_title',
                'match_number',
            ])
                ->where('status', 'upcoming')
                ->whereNotNull('scheduled_at')
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();
        });

        $announcement = Announcement::getActive();

        $overallStandings = cache()->remember('home_overall_standings', 120, function () {
            $sports = Sport::orderBy('order')->with('categories')->get();
            $teams = Team::orderBy('name')->get()->keyBy('id');

            $overallPoints = [];

            foreach ($sports as $sport) {
                foreach ($sport->categories as $category) {
                    $games = $category->games()
                        ->select(['id', 'category_id', 'team_home_id', 'team_away_id', 'score_home', 'score_away', 'game_data', 'status', 'winner_id', 'disqualified_team'])
                        ->where('status', 'completed')
                        ->get();

                    $categoryStandings = $this->computeStandings($games, $teams);

                    foreach ($categoryStandings as $row) {
                        if ($row['played'] === 0) {
                            continue;
                        }
                        $teamId = $row['team']->id;
                        if (! isset($overallPoints[$teamId])) {
                            $overallPoints[$teamId] = [
                                'team' => $row['team'],
                                'points' => 0,
                            ];
                        }
                        $overallPoints[$teamId]['points'] += $row['points'];
                    }
                }
            }

            uasort($overallPoints, fn ($a, $b) => $b['points'] <=> $a['points']);

            return array_values($overallPoints);
        });

        return view('home', compact('liveGames', 'recentResults', 'upcomingGames', 'announcement', 'overallStandings'));
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

            $gameData = $game->game_data ?? [];
            $places = $gameData['places'] ?? [];
            $disqualifiedTeamId = $game->disqualified_team ? (int) $game->disqualified_team : null;

            if (! empty($places) && is_array($places)) {
                foreach ($places as $place => $teamId) {
                    if ($teamId && isset($stats[$teamId])) {
                        $stats[$teamId]['played']++;
                        $stats[$teamId]['points'] += ($teamId == $disqualifiedTeamId)
                            ? 0
                            : $this->getPlacePoints((int) $place);
                    }
                }
                continue;
            }

            $homeId = $game->team_home_id;
            $awayId = $game->team_away_id;
            $scoreHome = $game->score_home ?? 0;
            $scoreAway = $game->score_away ?? 0;

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

            return $b['goal_difference'] - $a['goal_difference'];
        });

        return $stats;
    }
}
