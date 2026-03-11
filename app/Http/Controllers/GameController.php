<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function showByContext(Sport $sport, string $category, int $match): View
    {
        $categoryModel = Category::where('sport_id', $sport->id)
            ->where('slug', $category)
            ->firstOrFail();

        $game = Game::with(['category.sport', 'teamHome', 'teamAway', 'winner'])
            ->where('category_id', $categoryModel->id)
            ->where(function ($q) use ($match) {
                $q->where('match_number', $match)
                    ->orWhere(function ($q2) use ($match) {
                        $q2->whereNull('match_number')
                            ->where('id', $match);
                    });
            })
            ->firstOrFail();

        return $this->show($game);
    }

    public function show(Game $game): View
    {
        $game->load(['category.sport', 'teamHome', 'teamAway', 'winner']);

        return view('games.show', compact('game'));
    }

    public function edit(Game $game): View
    {
        $game->load(['category.sport', 'teamHome', 'teamAway', 'winner']);
        $teams = Team::orderBy('name')->get();

        // Initialize game_data if not set and sport supports it
        $sportConfig = $game->sport_config;
        if ($sportConfig && ! $game->game_data && $sportConfig['type'] !== 'time') {
            $game->game_format = $game->game_format ?? ($sportConfig['default_format'] ?? null);
            $game->game_data = $game->initializeGameData();
            $game->save();
        }

        return view('games.edit', compact('game', 'teams'));
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $rules = [
            'status' => ['required', 'in:upcoming,in_progress,completed'],
            'scheduled_at' => ['nullable', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'event_title' => ['nullable', 'string', 'max:100'],
        ];
        if (! $game->event_type) {
            $rules['score_home'] = ['nullable', 'integer', 'min:0'];
            $rules['score_away'] = ['nullable', 'integer', 'min:0'];
        }
        $validated = $request->validate($rules);

        // For events, ignore score fields
        if ($game->event_type) {
            unset($validated['score_home'], $validated['score_away']);
            $validated['winner_id'] = null;
        } else {
            // Auto-determine winner from scores when completed
            $validated['winner_id'] = null;
            if ($validated['status'] === 'completed' && isset($validated['score_home']) && isset($validated['score_away'])) {
                if ($validated['score_home'] > $validated['score_away']) {
                    $validated['winner_id'] = $game->team_home_id;
                } elseif ($validated['score_away'] > $validated['score_home']) {
                    $validated['winner_id'] = $game->team_away_id;
                }
                // Draw: winner_id stays null
            }
        }

        $game->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Game updated successfully.');
    }

    /**
     * Reset a game back to its initial (unplayed) state.
     */
    public function reset(Game $game): RedirectResponse
    {
        $game->score_home = null;
        $game->score_away = null;
        $game->game_data = null;
        $game->current_period = null;
        $game->status = 'upcoming';
        $game->winner_id = null;
        $game->notes = null;
        $game->save();

        return redirect()->route('games.edit', $game)
            ->with('success', 'Match has been reset successfully.');
    }

    /**
     * Live score update via AJAX. Saves game_data, recalculates totals, returns JSON.
     */
    public function liveUpdate(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'game_data' => ['nullable', 'array'],
            'game_format' => ['nullable', 'string', 'in:best_of_3,best_of_5'],
            'current_period' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:upcoming,in_progress,completed'],
            'score_home' => ['nullable', 'integer', 'min:0'],
            'score_away' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Load relationships needed for recalculateTotalsFromGameData()
        $game->load(['category.sport']);

        // If format changed and this is a set-based sport, reinitialize game data
        if (isset($validated['game_format']) && $validated['game_format'] !== $game->game_format) {
            $game->game_format = $validated['game_format'];
            $game->game_data = $game->initializeGameData();
        }

        if (isset($validated['game_data'])) {
            $game->game_data = $validated['game_data'];
        }

        if (isset($validated['current_period'])) {
            $game->current_period = $validated['current_period'];
        }

        if (isset($validated['status'])) {
            $game->status = $validated['status'];
        }

        if (isset($validated['notes'])) {
            $game->notes = $validated['notes'];
        }

        // If direct scores provided (custom override), use those
        if (array_key_exists('score_home', $validated) && array_key_exists('score_away', $validated)
            && $validated['score_home'] !== null && $validated['score_away'] !== null
            && ! isset($validated['game_data'])) {
            $game->score_home = $validated['score_home'];
            $game->score_away = $validated['score_away'];
        } else {
            // Always recalculate totals from game_data when game_data is provided
            // This ensures live scores stay in sync for all sport types (sets, quarters, halves)
            if (isset($validated['game_data'])) {
                $game->recalculateTotalsFromGameData();
            }
        }

        // Auto-determine winner when completed
        $game->winner_id = null;
        if ($game->status === 'completed' && $game->score_home !== null && $game->score_away !== null) {
            if ($game->score_home > $game->score_away) {
                $game->winner_id = $game->team_home_id;
            } elseif ($game->score_away > $game->score_home) {
                $game->winner_id = $game->team_away_id;
            }
        }

        $game->save();
        $game->load(['teamHome', 'teamAway', 'winner', 'category.sport']);

        return response()->json([
            'success' => true,
            'game' => [
                'id' => $game->id,
                'score_home' => $game->score_home,
                'score_away' => $game->score_away,
                'game_data' => $game->game_data,
                'game_format' => $game->game_format,
                'current_period' => $game->current_period,
                'status' => $game->status,
                'status_label' => $game->status_label,
                'winner_id' => $game->winner_id,
                'team_home' => [
                    'id' => $game->teamHome->id,
                    'name' => $game->teamHome->name,
                    'color_hex' => $game->teamHome->color_hex,
                ],
                'team_away' => [
                    'id' => $game->teamAway->id,
                    'name' => $game->teamAway->name,
                    'color_hex' => $game->teamAway->color_hex,
                ],
                'notes' => $game->notes,
                'updated_at' => $game->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Public polling endpoint for live game data. No auth required.
     */
    public function liveData(Game $game): JsonResponse
    {
        $game->load(['teamHome', 'teamAway', 'winner', 'category.sport']);

        return response()->json([
            'id' => $game->id,
            'score_home' => $game->score_home,
            'score_away' => $game->score_away,
            'game_data' => $game->game_data,
            'game_format' => $game->game_format,
            'current_period' => $game->current_period,
            'status' => $game->status,
            'status_label' => $game->status_label,
            'winner_id' => $game->winner_id,
            'team_home' => [
                'id' => $game->teamHome->id,
                'name' => $game->teamHome->name,
                'color_hex' => $game->teamHome->color_hex,
            ],
            'team_away' => [
                'id' => $game->teamAway->id,
                'name' => $game->teamAway->name,
                'color_hex' => $game->teamAway->color_hex,
            ],
            'sport_slug' => $game->sport_slug,
            'notes' => $game->notes,
            'updated_at' => $game->updated_at->toISOString(),
        ]);
    }

    /**
     * Batch live data for multiple games (for polling public pages).
     */
    public function batchLiveData(Request $request): JsonResponse
    {
        $ids = $request->query('ids', '');
        $gameIds = array_filter(array_map('intval', explode(',', $ids)));

        if (empty($gameIds) || count($gameIds) > 50) {
            return response()->json([]);
        }

        $games = Game::with(['teamHome', 'teamAway', 'winner', 'category.sport'])
            ->whereIn('id', $gameIds)
            ->get();

        $result = [];
        foreach ($games as $game) {
            $currentPeriod = $game->current_period;
            if (($game->game_data['halftime'] ?? false) && $game->sport_slug === 'basketball') {
                $currentPeriod = 'Halftime';
            }
            $result[$game->id] = [
                'id' => $game->id,
                'score_home' => $game->score_home,
                'score_away' => $game->score_away,
                'game_data' => $game->game_data,
                'game_format' => $game->game_format,
                'current_period' => $currentPeriod,
                'status' => $game->status,
                'status_label' => $game->status_label,
                'winner_id' => $game->winner_id,
                'team_home_name' => $game->teamHome->name,
                'team_away_name' => $game->teamAway->name,
                'updated_at' => $game->updated_at->toISOString(),
            ];
        }

        return response()->json($result);
    }
}
