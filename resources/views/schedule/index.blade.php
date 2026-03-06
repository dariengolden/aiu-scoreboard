@extends('layouts.app')

@section('title', 'Schedule')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-white mb-1">Schedule</h1>
        <p class="text-slate-400 text-sm">Upcoming, live, and past games</p>
    </div>

    {{-- Filters --}}
    @php
        $statusOptions = [
            'upcoming' => 'Upcoming',
            'live' => 'Live',
            'past' => 'Past',
        ];

        // Helper to build a URL that toggles a value in an array param (keeps panel open)
        function scheduleFilterUrl(array $selectedSports, array $selectedStatuses, ?string $toggleSport = null, ?string $toggleStatus = null): string {
            $sports = $selectedSports;
            $statuses = $selectedStatuses;

            if ($toggleSport !== null) {
                if (in_array($toggleSport, $sports)) {
                    $sports = array_values(array_diff($sports, [$toggleSport]));
                } else {
                    $sports[] = $toggleSport;
                }
            }

            if ($toggleStatus !== null) {
                if (in_array($toggleStatus, $statuses)) {
                    $statuses = array_values(array_diff($statuses, [$toggleStatus]));
                } else {
                    $statuses[] = $toggleStatus;
                }
            }

            $params = ['open=1'];
            foreach ($sports as $s) { $params[] = 'sport[]=' . urlencode($s); }
            foreach ($statuses as $st) { $params[] = 'status[]=' . urlencode($st); }

            return route('schedule') . '?' . implode('&', $params);
        }

        // Helper to build a URL that removes a single value (keeps panel open)
        function scheduleRemoveUrl(array $selectedSports, array $selectedStatuses, ?string $removeSport = null, ?string $removeStatus = null): string {
            $sports = $removeSport !== null ? array_values(array_diff($selectedSports, [$removeSport])) : $selectedSports;
            $statuses = $removeStatus !== null ? array_values(array_diff($selectedStatuses, [$removeStatus])) : $selectedStatuses;

            $params = ['open=1'];
            foreach ($sports as $s) { $params[] = 'sport[]=' . urlencode($s); }
            foreach ($statuses as $st) { $params[] = 'status[]=' . urlencode($st); }

            return route('schedule') . '?' . implode('&', $params);
        }

        $hasSportFilters = !empty($selectedSports);
        $hasStatusFilters = !empty($selectedStatuses);
        $hasAnyFilter = $hasSportFilters || $hasStatusFilters;
        $filterCount = count($selectedSports) + count($selectedStatuses);
        $panelOpen = request()->has('open') || $hasAnyFilter;
    @endphp

    <div class="mb-6">
        <div class="bg-[#1e293b] rounded-xl border border-white/5 overflow-hidden">
            {{-- Header row: always visible, acts as toggle --}}
            <div class="flex items-center gap-2 flex-wrap px-4 py-3 cursor-pointer select-none"
                 onclick="var p=document.getElementById('filter-body');p.classList.toggle('hidden');this.querySelector('.chevron').classList.toggle('rotate-180')">
                <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span class="text-sm font-semibold text-slate-200">Filters</span>
                @if($filterCount > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-blue-600 text-white text-xs font-bold leading-none">{{ $filterCount }}</span>
                @endif

                {{-- Active filter chips (inline in header) --}}
                @foreach($selectedStatuses as $key)
                    <a href="{{ scheduleRemoveUrl($selectedSports, $selectedStatuses, removeStatus: $key) }}"
                       onclick="event.stopPropagation()"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                        {{ $statusOptions[$key] ?? $key }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endforeach
                @foreach($selectedSports as $slug)
                    @php $sportObj = $sports->firstWhere('slug', $slug); @endphp
                    @if($sportObj)
                    <a href="{{ scheduleRemoveUrl($selectedSports, $selectedStatuses, removeSport: $slug) }}"
                       onclick="event.stopPropagation()"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                        {{ $sportObj->icon }} {{ $sportObj->name }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                @endforeach

                {{-- Spacer + clear all + divider + chevron --}}
                <div class="flex-1"></div>
                @if($hasAnyFilter)
                <a href="{{ route('schedule') }}" onclick="event.stopPropagation()" class="text-xs text-slate-400 hover:text-white transition-colors font-medium px-2 py-1 rounded-lg hover:bg-white/5">
                    Clear all
                </a>
                <div class="w-px h-5 bg-white/10 shrink-0"></div>
                @endif
                <div class="shrink-0 p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                    <svg class="chevron w-3.5 h-3.5 text-slate-400 transition-transform {{ $panelOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            {{-- Filter body: expands below the header within the same card --}}
            <div id="filter-body" class="{{ $panelOpen ? '' : 'hidden' }}">
                <div class="border-t border-white/5"></div>

                {{-- Status section --}}
                <div class="px-4 pt-3 pb-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</span>
                </div>
                <div class="px-4 pb-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach($statusOptions as $key => $label)
                            @php $isActive = in_array($key, $selectedStatuses); @endphp
                        <a href="{{ scheduleFilterUrl($selectedSports, $selectedStatuses, toggleStatus: $key) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $isActive ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-white/5 mx-4"></div>

                {{-- Sport section --}}
                <div class="px-4 pt-3 pb-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sport</span>
                </div>
                <div class="px-4 pb-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($sports as $sport)
                            @php $isActive = in_array($sport->slug, $selectedSports); @endphp
                        <a href="{{ scheduleFilterUrl($selectedSports, $selectedStatuses, toggleSport: $sport->slug) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $isActive ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white' }}">
                            {{ $sport->icon }} {{ $sport->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar View --}}
    @if($games->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">📅</div>
        <p class="text-slate-400 font-medium">No games found.</p>
        <p class="text-slate-500 text-sm mt-1">Try adjusting your filters or check back soon!</p>
    </div>
    @else

    @php
        $today = \Carbon\Carbon::today();
    @endphp

    {{-- ===================== --}}
    {{-- DESKTOP CALENDAR GRID --}}
    {{-- ===================== --}}
    <div class="hidden md:block">
        {{-- Day-of-week header --}}
        <div class="grid grid-cols-7 mb-2">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
            <div class="text-center text-xs font-semibold uppercase tracking-wider text-slate-500 py-2">
                {{ $dayName }}
            </div>
            @endforeach
        </div>

        {{-- Calendar weeks --}}
        @foreach($calendarWeeks as $week)
        <div class="grid grid-cols-7 border-t border-white/5">
            @foreach($week as $date)
            @php
                $dateKey = $date->format('Y-m-d');
                $dayGames = $gamesByDate->get($dateKey, collect());
                $isToday = $date->isSameDay($today);
                $hasGames = $dayGames->isNotEmpty();
            @endphp
            <div class="border-r border-b border-white/5 first:border-l min-h-[140px] flex flex-col {{ $isToday ? 'bg-blue-600/[0.07]' : ($hasGames ? 'bg-[#1e293b]/50' : 'bg-[#0f172a]/50') }}">
                {{-- Date number --}}
                <div class="px-2 pt-2 pb-1 flex items-center justify-between">
                    <span class="text-sm font-bold {{ $isToday ? 'text-blue-400' : ($hasGames ? 'text-slate-200' : 'text-slate-600') }}">
                        @if($date->day === 1 || $date->isSameDay($calendarStart))
                            {{ $date->format('M j') }}
                        @else
                            {{ $date->day }}
                        @endif
                    </span>
                    @if($isToday)
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Today</span>
                    @endif
                </div>

                {{-- Games list --}}
                @if($hasGames)
                <div class="px-1.5 pb-1.5 flex-1 space-y-0.5 overflow-y-auto">
                    @foreach($dayGames as $game)
                    @php
                        $statusColor = match($game->status) {
                            'in_progress' => 'border-l-green-500 bg-green-500/10',
                            'completed' => 'border-l-blue-500/50 bg-blue-500/5',
                            default => 'border-l-slate-600 bg-white/[0.03]',
                        };
                    @endphp
                    <div class="border-l-2 {{ $statusColor }} rounded-r px-1.5 py-1 group cursor-default" title="{{ $game->category->sport->name }} {{ $game->category->name }} — {{ $game->teamHome->name }} vs {{ $game->teamAway->name }} @ {{ $game->scheduled_at->format('g:ia') }}{{ $game->location ? ' · ' . $game->location : '' }}">
                        <div class="flex items-center gap-1 min-w-0">
                            <span class="text-[11px] shrink-0">{{ $game->category->sport->icon }}</span>
                            <span class="text-[11px] text-slate-400 truncate leading-tight">
                                <span class="font-medium text-slate-300">{{ $game->scheduled_at->format('g:ia') }}</span>
                                {{ $game->category->name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                            <span class="text-[10px] text-slate-400 truncate leading-tight">
                                {{ $game->teamHome->name }}
                                <span class="text-slate-600">vs</span>
                                {{ $game->teamAway->name }}
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamAway->color_hex }}"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- ========================= --}}
    {{-- MOBILE CALENDAR (STACKED) --}}
    {{-- ========================= --}}
    <div class="md:hidden space-y-3">
        @foreach($calendarWeeks as $week)
            @foreach($week as $date)
            @php
                $dateKey = $date->format('Y-m-d');
                $dayGames = $gamesByDate->get($dateKey, collect());
                $isToday = $date->isSameDay($today);
                $hasGames = $dayGames->isNotEmpty();
            @endphp

            @if($hasGames)
            <div class="rounded-2xl border border-white/5 overflow-hidden {{ $isToday ? 'bg-blue-600/[0.07] border-blue-500/20' : 'bg-[#1e293b]' }}">
                {{-- Day header --}}
                <div class="px-4 py-3 flex items-center justify-between border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl {{ $isToday ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300' }}">
                            <span class="text-[10px] font-semibold uppercase leading-none tracking-wide {{ $isToday ? 'text-blue-200' : 'text-slate-500' }}">{{ $date->format('D') }}</span>
                            <span class="text-lg font-bold leading-tight">{{ $date->day }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-bold {{ $isToday ? 'text-blue-400' : 'text-white' }}">{{ $date->format('l, M j') }}</span>
                            @if($isToday)
                            <span class="ml-2 text-[10px] font-bold uppercase tracking-wider text-blue-400">Today</span>
                            @endif
                            <div class="text-xs text-slate-500">{{ $dayGames->count() }} {{ Str::plural('game', $dayGames->count()) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Games for this day --}}
                <div class="divide-y divide-white/5">
                    @foreach($dayGames as $game)
                    <div class="px-4 py-3">
                        <div class="flex items-start gap-3">
                            {{-- Time column --}}
                            <div class="shrink-0 w-14 pt-0.5 text-right">
                                <span class="text-xs font-semibold {{ $game->status === 'in_progress' ? 'text-green-400' : 'text-slate-400' }}">
                                    {{ $game->scheduled_at->format('g:ia') }}
                                </span>
                            </div>

                            {{-- Live indicator --}}
                            @if($game->status === 'in_progress')
                            <div class="shrink-0 mt-1.5">
                                <span class="block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            </div>
                            @endif

                            {{-- Game info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">{{ $game->category->sport->icon }}</span>
                                    <span class="text-sm font-semibold text-slate-200 truncate">{{ $game->category->sport->name }}</span>
                                    <span class="text-xs text-slate-500">&middot;</span>
                                    <span class="text-xs text-slate-400 truncate">{{ $game->category->name }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamHome->color_hex }}">{{ $game->teamHome->name }}</span>
                                    <span class="text-xs text-slate-600 font-bold">VS</span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamAway->color_hex }}">{{ $game->teamAway->name }}</span>
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamAway->color_hex }}"></span>
                                </div>
                                @if($game->location)
                                <div class="flex items-center gap-1 mt-1 text-[11px] text-slate-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    {{ $game->location }}
                                </div>
                                @endif
                                @if($game->status === 'in_progress')
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-500 text-white animate-pulse">LIVE</span>
                                </div>
                                @elseif($game->status === 'completed')
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/30 text-blue-300">Final</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @endforeach
        @endforeach
    </div>

    @endif

</div>

@endsection
