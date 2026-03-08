<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $sports = Sport::orderBy('order')->get();
        $teams = Team::orderBy('name')->get();

        $selectedSports = array_filter((array) $request->input('sport', []));
        $selectedStatuses = array_filter((array) $request->input('status', []));
        $selectedColors = array_filter((array) $request->input('color', []));

        // Date range is global for all games; cache it independently of filters
        $dateRange = cache()->remember('schedule_dates', 3600, function () {
            return Game::whereNotNull('scheduled_at')
                ->selectRaw('MIN(scheduled_at) as first_date, MAX(scheduled_at) as last_date')
                ->first();
        });

        if ($dateRange->first_date && $dateRange->last_date) {
            $firstDate = Carbon::parse($dateRange->first_date)->startOfDay();
            $lastDate = Carbon::parse($dateRange->last_date)->startOfDay();

            $calendarStart = $firstDate->copy()->startOfWeek(Carbon::SUNDAY);
            $calendarEnd = $lastDate->copy()->endOfWeek(Carbon::SATURDAY);
        } else {
            $calendarStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $calendarEnd = $calendarStart->copy()->addDays(13)->endOfWeek(Carbon::SATURDAY);
        }

        // Select only the columns needed for schedule display to avoid decoding large JSON fields
        $query = Game::select([
                'id',
                'category_id',
                'match_number',
                'team_home_id',
                'team_away_id',
                'score_home',
                'score_away',
                'current_period',
                'status',
                'scheduled_at',
                'location',
                'event_type',
                'event_title',
            ])
            ->with(['category.sport', 'teamHome', 'teamAway'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$calendarStart, $calendarEnd]);

        if (! empty($selectedStatuses)) {
            $statusMap = [
                'upcoming' => 'upcoming',
                'live' => 'in_progress',
                'past' => 'completed',
            ];
            $dbStatuses = array_values(array_intersect_key($statusMap, array_flip($selectedStatuses)));
            if (! empty($dbStatuses)) {
                $query->whereIn('status', $dbStatuses);
            }
        }

        if (! empty($selectedSports)) {
            $query->whereHas('category.sport', fn ($q) => $q->whereIn('slug', $selectedSports));
        }

        if (! empty($selectedColors)) {
            $query->where(function ($q) use ($selectedColors) {
                $q->whereIn('team_home_id', $selectedColors)
                    ->orWhereIn('team_away_id', $selectedColors);
            });
        }

        // Cache per-filter game lists for a short period to smooth out bursts of filter requests
        $cacheKey = 'schedule_'.md5(serialize($selectedSports).serialize($selectedStatuses).serialize($selectedColors));
        $games = cache()->remember($cacheKey.'_games', 30, function () use ($query) {
            return $query->orderByRaw("CASE WHEN event_type IS NOT NULL THEN 0 ELSE 1 END")
                ->orderBy('scheduled_at')
                ->get();
        });

        $gamesByDate = $games->groupBy(fn ($game) => $game->scheduled_at->format('Y-m-d'));
        $gamesByDateResource = $gamesByDate->map(
            fn ($dayGames) => GameResource::collection($dayGames->values())->resolve()
        );

        $calendarDates = collect(CarbonPeriod::create($calendarStart, $calendarEnd))
            ->map(fn ($date) => $date->copy());

        $calendarWeeks = $calendarDates->chunk(7);

        return view('schedule.index', compact(
            'sports',
            'teams',
            'games',
            'selectedSports',
            'selectedStatuses',
            'selectedColors',
            'gamesByDate',
            'gamesByDateResource',
            'calendarWeeks',
            'calendarStart',
            'calendarEnd',
        ));
    }

    public function api(Request $request): JsonResponse
    {
        $selectedSports = array_filter((array) $request->input('sport', []));
        $selectedStatuses = array_filter((array) $request->input('status', []));
        $selectedColors = array_filter((array) $request->input('color', []));
        $start = $request->input('start');
        $end = $request->input('end');

        $cacheKey = 'schedule_api_'.md5(serialize($selectedSports).serialize($selectedStatuses).serialize($selectedColors).$start.$end);

        $games = cache()->remember($cacheKey, 30, function () use ($selectedSports, $selectedStatuses, $selectedColors, $start, $end) {
            $query = Game::select([
                    'id',
                    'category_id',
                    'match_number',
                    'team_home_id',
                    'team_away_id',
                    'score_home',
                    'score_away',
                    'current_period',
                    'status',
                    'scheduled_at',
                    'location',
                    'event_type',
                    'event_title',
                ])
                ->with(['category.sport', 'teamHome', 'teamAway'])
                ->whereNotNull('scheduled_at');

            if ($start && $end) {
                $query->whereBetween('scheduled_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
            }

            if (! empty($selectedStatuses)) {
                $statusMap = [
                    'upcoming' => 'upcoming',
                    'live' => 'in_progress',
                    'past' => 'completed',
                ];
                $dbStatuses = array_values(array_intersect_key($statusMap, array_flip($selectedStatuses)));
                if (! empty($dbStatuses)) {
                    $query->whereIn('status', $dbStatuses);
                }
            }

            if (! empty($selectedSports)) {
                $query->whereHas('category.sport', fn ($q) => $q->whereIn('slug', $selectedSports));
            }

            if (! empty($selectedColors)) {
                $query->where(function ($q) use ($selectedColors) {
                    $q->whereIn('team_home_id', $selectedColors)
                        ->orWhereIn('team_away_id', $selectedColors);
                });
            }

            return $query->orderByRaw("CASE WHEN event_type IS NOT NULL THEN 0 ELSE 1 END")
                ->orderBy('scheduled_at')
                ->get();
        });

        $gamesByDate = $games->groupBy(fn ($game) => $game->scheduled_at->format('Y-m-d'));

        return response()->json([
            'games' => GameResource::collection($games),
            'gamesByDate' => $gamesByDate->map(fn ($dayGames) => GameResource::collection($dayGames->values())->resolve()),
        ]);
    }
}
