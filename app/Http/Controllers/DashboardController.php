<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Sport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sports = Sport::orderBy('order')->with(['categories.games' => function ($q) {
            $q->with(['teamHome', 'teamAway', 'winner']);
        }])->get();

        $liveGames = Game::where('status', 'in_progress')
            ->with(['category.sport', 'teamHome', 'teamAway'])
            ->get();

        $totalGames = Game::count();
        $completedGames = Game::where('status', 'completed')->count();
        $upcomingGames = Game::where('status', 'upcoming')->count();

        return view('dashboard.index', compact('sports', 'liveGames', 'totalGames', 'completedGames', 'upcomingGames'));
    }
}
