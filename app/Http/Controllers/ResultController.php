<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $sports = Sport::orderBy('order')->get();
        $selectedSport = $request->input('sport');

        $query = Game::with(['category.sport', 'teamHome', 'teamAway', 'winner'])
            ->where('status', 'completed');

        if ($selectedSport) {
            $query->whereHas('category.sport', fn ($q) => $q->where('slug', $selectedSport));
        }

        $games = $query->orderByDesc('updated_at')->get();

        return view('results.index', compact('sports', 'games', 'selectedSport'));
    }
}
