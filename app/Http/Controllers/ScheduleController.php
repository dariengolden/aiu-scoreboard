<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Sport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $sports = Sport::orderBy('order')->get();

        $selectedSports = array_filter((array) $request->input('sport', []));
        $selectedStatuses = array_filter((array) $request->input('status', []));

        $query = Game::with(['category.sport', 'teamHome', 'teamAway'])
            ->whereNotNull('scheduled_at');

        // When no status filter is selected, show all statuses
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

        $games = $query->orderBy('scheduled_at')->get();

        // Group games by date (Y-m-d) for the calendar view
        $gamesByDate = $games->groupBy(fn ($game) => $game->scheduled_at->format('Y-m-d'));

        // Determine the two-week calendar range from the earliest game date
        // Fallback to current date if no games exist
        $dateRange = Game::whereNotNull('scheduled_at')
            ->selectRaw('MIN(scheduled_at) as first_date, MAX(scheduled_at) as last_date')
            ->first();

        if ($dateRange->first_date && $dateRange->last_date) {
            $firstDate = Carbon::parse($dateRange->first_date)->startOfDay();
            $lastDate = Carbon::parse($dateRange->last_date)->startOfDay();

            // Start from the Sunday (start of week) on or before the first game
            $calendarStart = $firstDate->copy()->startOfWeek(Carbon::SUNDAY);
            // End on the Saturday (end of week) on or after the last game
            $calendarEnd = $lastDate->copy()->endOfWeek(Carbon::SATURDAY);
        } else {
            $calendarStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $calendarEnd = $calendarStart->copy()->addDays(13)->endOfWeek(Carbon::SATURDAY);
        }

        // Build the array of dates for the calendar
        $calendarDates = collect(CarbonPeriod::create($calendarStart, $calendarEnd))
            ->map(fn ($date) => $date->copy());

        // Split into weeks for the grid
        $calendarWeeks = $calendarDates->chunk(7);

        return view('schedule.index', compact(
            'sports',
            'games',
            'selectedSports',
            'selectedStatuses',
            'gamesByDate',
            'calendarWeeks',
            'calendarStart',
            'calendarEnd',
        ));
    }
}
