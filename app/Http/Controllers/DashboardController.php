<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Sport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sports = cache()->remember('dashboard_sports_tree', 10, function () {
            return Sport::orderBy('order')->with(['categories.games' => function ($q) {
                $q->with(['teamHome', 'teamAway', 'winner']);
            }])->get();
        });

        $liveGames = cache()->remember('dashboard_live_games', 5, function () {
            return Game::where('status', 'in_progress')
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->get();
        });

        $totals = cache()->remember('dashboard_game_counts', 5, function () {
            return [
                'total' => Game::count(),
                'completed' => Game::where('status', 'completed')->count(),
                'upcoming' => Game::where('status', 'upcoming')->count(),
            ];
        });

        $totalGames = $totals['total'];
        $completedGames = $totals['completed'];
        $upcomingGames = $totals['upcoming'];

        return view('dashboard.index', compact('sports', 'liveGames', 'totalGames', 'completedGames', 'upcomingGames'));
    }
}
