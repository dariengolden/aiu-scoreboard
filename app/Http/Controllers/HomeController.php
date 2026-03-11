<?php

namespace App\Http\Controllers;

use App\Models\Game;
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

        return view('home', compact('liveGames', 'recentResults', 'upcomingGames'));
    }
}
