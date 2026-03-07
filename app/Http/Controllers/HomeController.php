<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $liveGames = cache()->remember('home_live_games', 5, function () {
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
                ])
                ->where('status', 'in_progress')
                ->with(['category.sport', 'teamHome', 'teamAway'])
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
                ])
                ->where('status', 'upcoming')
                ->whereNotNull('scheduled_at')
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();
        });

        return view('home', compact('liveGames', 'upcomingGames'));
    }
}
