<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $liveGames = Game::where('status', 'in_progress')
            ->with(['category.sport', 'teamHome', 'teamAway'])
            ->get();

        $upcomingGames = Game::where('status', 'upcoming')
            ->whereNotNull('scheduled_at')
            ->with(['category.sport', 'teamHome', 'teamAway'])
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        return view('home', compact('liveGames', 'upcomingGames'));
    }
}
