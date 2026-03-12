@extends('layouts.app')
@section('title', 'Schedule')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-white mb-1">Schedule</h1>
    </div>
    {{-- Filters --}}
    @php
        $statusOptions = [
            'upcoming' => 'Upcoming',
            'live' => 'Live',
            'past' => 'Past',
        ];
        $selectedSports = $selectedSports ?? [];
        $selectedStatuses = $selectedStatuses ?? [];
        $selectedColors = $selectedColors ?? [];
        $gamesByDate = $gamesByDate ?? collect();
        $calendarWeeks = $calendarWeeks ?? collect();
        $calendarStart = $calendarStart ?? now()->startOfWeek(Carbon\Carbon::SUNDAY);
        $calendarEnd = $calendarEnd ?? $calendarStart->copy()->addDays(13)->endOfWeek(Carbon\Carbon::SATURDAY);
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
        function scheduleRemoveUrl(array $selectedSports, array $selectedStatuses, ?string $removeSport = null, ?string $removeStatus = null): string {
            $sports = $removeSport !== null ? array_values(array_diff($selectedSports, [$removeSport])) : $selectedSports;
            $statuses = $removeStatus !== null ? array_values(array_diff($selectedStatuses, [$removeStatus])) : $selectedStatuses;
            $params = ['open=1'];
            foreach ($sports as $s) { $params[] = 'sport[]=' . urlencode($s); }
            foreach ($statuses as $st) { $params[] = 'status[]=' . urlencode($st); }
            return route('schedule') . '?' . implode('&', $params);
        }
        function buildFilterParams(array $selectedSports, array $selectedStatuses): array {
            $params = [];
            foreach ($selectedSports as $s) { $params['sport'][] = $s; }
            foreach ($selectedStatuses as $st) { $params['status'][] = $st; }
            return $params;
        }
        $hasSportFilters = !empty($selectedSports);
        $hasStatusFilters = !empty($selectedStatuses);
        $hasColorFilters = !empty($selectedColors);
        $hasAnyFilter = $hasSportFilters || $hasStatusFilters || $hasColorFilters;
        $filterCount = count($selectedSports) + count($selectedStatuses) + count($selectedColors);
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
                <span id="active-filters" class="text-xs text-slate-400">
                @if($filterCount > 0)
                    {{ $filterCount }} applied
                @endif
                </span>
                {{-- Spacer + clear all + divider + chevron --}}
                <div class="flex-1"></div>
                <a href="{{ route('schedule') }}" id="clear-all-btn" onclick="event.preventDefault(); clearAllFilters()" class="hidden text-xs text-slate-400 hover:text-white transition-colors font-medium px-2 py-1 rounded-lg hover:bg-white/5">
                    Clear all
                </a>
                <div id="clear-all-divider" class="hidden w-px h-5 bg-white/10 shrink-0"></div>
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
                           data-filter-status="{{ $key }}"
                           onclick="event.preventDefault(); toggleFilter('status', '{{ $key }}')"
                           class="filter-btn px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $isActive ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white' }}">
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
                           data-filter-sport="{{ $sport->slug }}"
                           onclick="event.preventDefault(); toggleFilter('sport', '{{ $sport->slug }}')"
                           class="filter-btn px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $isActive ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white' }}">
                            <x-sport-icon :sport="$sport" size="sm" /> {{ $sport->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                {{-- Divider --}}
                <div class="border-t border-white/5 mx-4"></div>
                {{-- Color/Team section --}}
                <div class="px-4 pt-3 pb-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Team Color</span>
                </div>
                <div class="px-4 pb-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($teams as $team)
                            @php $isActive = in_array($team->id, $selectedColors ?? []); @endphp
                        <a href="#"
                           data-filter-color="{{ $team->id }}"
                           onclick="event.preventDefault(); toggleFilter('color', '{{ $team->id }}')"
                           class="filter-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $isActive ? 'bg-blue-600 text-white' : 'bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white' }}">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $team->color_hex }}"></span>
                            {{ $team->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Loading indicator --}}
    <div id="schedule-loading" class="hidden text-center py-20">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        <p class="text-slate-400 mt-4">Loading games...</p>
    </div>
    {{-- Calendar View --}}
    <div id="schedule-content">
    @if($games->isEmpty())
    <div class="text-center py-20" id="empty-state">
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
    <div id="desktop-calendar" class="hidden md:block">
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
            <div class="border-r border-b border-white/5 first:border-l min-h-[140px] flex flex-col {{ $isToday ? 'bg-sky-500/15' : ($hasGames ? 'bg-[#1e293b]/50' : 'bg-[#0f172a]/50') }}">
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
                    @php
                        $gameTitle = $game->event_title ?: ($game->teamHome?->name ?? 'TBD') . ' vs ' . ($game->teamAway?->name ?? 'TBD');
                        $sportConfig = $game->sport_config;
                        $sportType = $sportConfig['type'] ?? null;
                        $gameData = $game->game_data ?? [];
                        $isLive = $game->isLiveOrEventLive();
                        $periodLabels = $game->period_labels ?? [];
                        $breakdownItems = [];
                        if ($sportType === 'sets' || $sportType === 'quarters' || $sportType === 'halves') {
                            $dataKey = $sportType === 'sets' ? 'sets' : 'periods';
                            $items = $gameData[$dataKey] ?? [];
                            $breakdownItems = array_filter($items, fn($item) => ($item['home'] ?? 0) > 0 || ($item['away'] ?? 0) > 0);
                        }
                    @endphp
                    <a href="{{ route('games.show', ['sport' => $game->category?->sport?->slug, 'category' => $game->category?->slug, 'match' => $game->match_number ?? $game->id]) }}"
                       onclick="event.preventDefault(); openGameModal({{ $game->id }})"
                       data-game-id="{{ $game->id }}"
                       data-sport-icon="{{ $game->category?->sport?->icon ?? '' }}"
                       data-sport-name="{{ $game->category?->sport?->name ?? '' }}"
                       data-category-name="{{ $game->category?->name ?? '' }}"
                       data-match-label="{{ $game->match_label ?? '' }}"
                       data-home-name="{{ $game->teamHome?->name ?? 'TBD' }}"
                       data-home-color="{{ $game->teamHome?->color_hex ?? '#94a3b8' }}"
                       data-away-name="{{ $game->teamAway?->name ?? 'TBD' }}"
                       data-away-color="{{ $game->teamAway?->color_hex ?? '#94a3b8' }}"
                       data-score-home="{{ $game->score_home ?? '' }}"
                       data-score-away="{{ $game->score_away ?? '' }}"
                       data-status="{{ $game->status }}"
                       data-current-period="{{ $game->current_period ?? '' }}"
                       data-scheduled-at="{{ $game->scheduled_at?->format('D, M j · g:ia') }}"
                       data-location="{{ $game->location ?? '' }}"
                       data-game-url="{{ route('games.show', ['sport' => $game->category?->sport?->slug, 'category' => $game->category?->slug, 'match' => $game->match_number ?? $game->id]) }}"
                       data-breakdown='@json($breakdownItems)'
                       data-period-labels='@json($periodLabels)'
                       class="block border-l-2 {{ $statusColor }} rounded-r px-1.5 py-1 group hover:bg-white/5 transition-colors cursor-pointer"
                       title="{{ $game->category?->sport?->name ?? '' }} {{ $game->category?->name ?? '' }} — {{ $gameTitle }} @ {{ $game->scheduled_at?->format('g:ia') }}{{ $game->location ? ' · ' . $game->location : '' }}">
                        <div class="flex items-center gap-1 min-w-0">
                            <x-sport-icon :sport="$game->category?->sport" size="xs" />
                            <span class="text-[11px] text-slate-400 truncate leading-tight">
                                <span class="font-medium text-slate-300">{{ $game->scheduled_at?->format('g:ia') }}</span>
                                {{ $game->category?->name ?? '' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5 min-w-0">
                            @if($game->event_title)
                            <span class="text-[10px] text-slate-300 truncate leading-tight font-medium">{{ $game->event_title }}</span>
                            @else
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamHome?->color_hex ?? '#94a3b8' }}"></span>
                            <span class="text-[10px] text-slate-400 truncate leading-tight">
                                {{ $game->teamHome?->name ?? 'TBD' }}
                                <span class="text-slate-600">vs</span>
                                {{ $game->teamAway?->name ?? 'TBD' }}
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamAway?->color_hex ?? '#94a3b8' }}"></span>
                            @endif
                        </div>
                    </a>
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
    <div id="mobile-calendar" class="md:hidden space-y-3">
        @foreach($calendarWeeks as $week)
            @foreach($week as $date)
            @php
                $dateKey = $date->format('Y-m-d');
                $dayGames = $gamesByDate->get($dateKey, collect());
                $isToday = $date->isSameDay($today);
                $hasGames = $dayGames->isNotEmpty();
            @endphp
            @if($hasGames)
            <div class="rounded-2xl border border-white/5 overflow-hidden {{ $isToday ? 'bg-sky-500/10 border-sky-500/30' : 'bg-[#1e293b]' }}">
                {{-- Day header --}}
                <div class="px-4 py-3 flex items-center justify-between border-b border-white/5">
                        <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl {{ $isToday ? 'bg-blue-400 text-slate-900' : 'bg-[#0f172a] text-slate-300' }}">
                            <span class="text-[10px] font-semibold uppercase leading-none tracking-wide {{ $isToday ? 'text-blue-900/70' : 'text-slate-500' }}">{{ $date->format('D') }}</span>
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
                    @php
                        $sportConfig = $game->sport_config;
                        $sportType = $sportConfig['type'] ?? null;
                        $gameData = $game->game_data ?? [];
                        $periodLabels = $game->period_labels ?? [];
                        $breakdownItems = [];
                        if ($sportType === 'sets' || $sportType === 'quarters' || $sportType === 'halves') {
                            $dataKey = $sportType === 'sets' ? 'sets' : 'periods';
                            $items = $gameData[$dataKey] ?? [];
                            $breakdownItems = array_filter($items, fn($item) => ($item['home'] ?? 0) > 0 || ($item['away'] ?? 0) > 0);
                        }
                    @endphp
                    <a href="{{ route('games.show', ['sport' => $game->category?->sport?->slug, 'category' => $game->category?->slug, 'match' => $game->match_number ?? $game->id]) }}"
                       onclick="event.preventDefault(); openGameModal({{ $game->id }})"
                       data-game-id="{{ $game->id }}"
                       data-sport-icon="{{ $game->category?->sport?->icon ?? '' }}"
                       data-sport-name="{{ $game->category?->sport?->name ?? '' }}"
                       data-category-name="{{ $game->category?->name ?? '' }}"
                       data-match-label="{{ $game->match_label ?? '' }}"
                       data-home-name="{{ $game->teamHome?->name ?? 'TBD' }}"
                       data-home-color="{{ $game->teamHome?->color_hex ?? '#94a3b8' }}"
                       data-away-name="{{ $game->teamAway?->name ?? 'TBD' }}"
                       data-away-color="{{ $game->teamAway?->color_hex ?? '#94a3b8' }}"
                       data-score-home="{{ $game->score_home ?? '' }}"
                       data-score-away="{{ $game->score_away ?? '' }}"
                       data-status="{{ $game->status }}"
                       data-current-period="{{ $game->current_period ?? '' }}"
                       data-scheduled-at="{{ $game->scheduled_at?->format('D, M j · g:ia') }}"
                       data-location="{{ $game->location ?? '' }}"
                       data-game-url="{{ route('games.show', ['sport' => $game->category?->sport?->slug, 'category' => $game->category?->slug, 'match' => $game->match_number ?? $game->id]) }}"
                       data-breakdown='@json($breakdownItems)'
                       data-period-labels='@json($periodLabels)'
                       class="block px-4 py-3 hover:bg-white/5 transition-colors cursor-pointer">
                        <div class="flex items-start gap-3">
                            {{-- Time column --}}
                            <div class="shrink-0 w-14 pt-0.5 text-right">
                                <span class="text-xs font-semibold {{ $game->status === 'in_progress' ? 'text-green-400' : 'text-slate-400' }}">
                                    {{ $game->scheduled_at?->format('g:ia') }}
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
                                    <x-sport-icon :sport="$game->category?->sport" size="sm" />
                                    <span class="text-sm font-semibold text-slate-200 truncate">{{ $game->category?->sport?->name ?? '' }}</span>
                                    <span class="text-xs text-slate-500">&middot;</span>
                                    <span class="text-xs text-slate-400 truncate">{{ $game->category?->name ?? '' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    @if($game->event_title)
                                    <span class="text-xs font-semibold text-slate-200">{{ $game->event_title }}</span>
                                    @else
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamHome?->color_hex ?? '#94a3b8' }}"></span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamHome?->color_hex ?? '#94a3b8' }}">{{ $game->teamHome?->name ?? 'TBD' }}</span>
                                    <span class="text-xs text-slate-600 font-bold">VS</span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamAway?->color_hex ?? '#94a3b8' }}">{{ $game->teamAway?->name ?? 'TBD' }}</span>
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamAway?->color_hex ?? '#94a3b8' }}"></span>
                                    @endif
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
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        @endforeach
    </div>
    @endif
    </div>
</div>

{{-- Back to Top Button --}}
<button id="back-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-28 md:bottom-6 md:right-6 right-4 z-50 p-3 rounded-full bg-slate-200 text-slate-900 shadow-lg transition-all duration-300 hover:bg-white" style="display: none;">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

{{-- Game Preview Modal --}}
<div id="game-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" onclick="closeGameModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-sm bg-[#1e293b] border border-white/5 rounded-2xl shadow-2xl shadow-black/60 transform transition-all duration-200 scale-95 opacity-0 overflow-hidden" id="game-modal-panel">

            {{-- Header: Sport + Category + Status + Close --}}
            <div class="px-4 pt-3 pb-2">
                <div class="flex items-center justify-between mb-1">
                    <span id="modal-sport-category" class="text-xs font-medium text-slate-400 truncate"></span>
                    <div class="flex items-center gap-2 shrink-0">
                        <span id="modal-status"></span>
                        <button onclick="closeGameModal()" class="p-1 rounded-lg text-slate-500 hover:text-white hover:bg-white/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider" id="modal-match-label"></span>
                    <span class="text-xs font-bold text-blue-400" id="modal-period"></span>
                </div>
            </div>

            {{-- Teams + Score --}}
            <div class="px-4 pb-3">
                {{-- Home team --}}
                <div class="flex items-center justify-between py-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-3 h-3 rounded-full shrink-0" id="modal-home-color"></span>
                        <span class="font-semibold text-sm truncate text-slate-300" id="modal-home-name"></span>
                    </div>
                    <span class="text-lg font-bold tabular-nums text-slate-400" id="modal-score-home">—</span>
                </div>

                <div class="border-t border-white/5 mx-0"></div>

                {{-- Away team --}}
                <div class="flex items-center justify-between py-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-3 h-3 rounded-full shrink-0" id="modal-away-color"></span>
                        <span class="font-semibold text-sm truncate text-slate-300" id="modal-away-name"></span>
                    </div>
                    <span class="text-lg font-bold tabular-nums text-slate-400" id="modal-score-away">—</span>
                </div>

                {{-- Period breakdown --}}
                <div id="modal-breakdown" class="hidden mt-2 pt-2 border-t border-white/5">
                    <div class="flex items-center gap-1.5 text-xs tabular-nums" id="modal-breakdown-content"></div>
                </div>
            </div>

            {{-- Footer: time + location + clickable area --}}
            <a href="#" id="modal-match-link" class="block px-4 py-3 bg-white/[0.03] border-t border-white/5 hover:bg-white/[0.06] transition-colors cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span class="flex items-center gap-1" id="modal-time"></span>
                        <span class="flex items-center gap-1 hidden" id="modal-location"></span>
                    </div>
                    <span class="text-xs font-bold text-blue-400">View full breakdown</span>
                </div>
            </a>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    let currentSports = @json($selectedSports ?? []);
    let currentStatuses = @json($selectedStatuses ?? []);
    let currentColors = @json($selectedColors ?? []);
    const calendarStart = '{{ $calendarStart->format("Y-m-d") }}';
    const calendarEnd = '{{ $calendarEnd->format("Y-m-d") }}';
    const sportsData = @json($sports->map(fn($s) => ['slug' => $s->slug, 'name' => $s->name, 'icon' => $s->icon]));
    const customIconSlugs = ['takraw'];
    const teamsData = @json($teams->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'colorHex' => $t->color_hex]));
    const initialGamesByDate = @json($gamesByDateResource ?? []);

    window.openGameModal = function(gameId) {
        const link = document.querySelector(`a[data-game-id="${gameId}"]`);
        if (!link) return;
        const modal = document.getElementById('game-modal');
        const modalPanel = document.getElementById('game-modal-panel');

        // Sport & category
        const sportIcon = link.dataset.sportIcon || '';
        const sportName = link.dataset.sportName || '';
        const categoryName = link.dataset.categoryName || '';
        const sportDataMatch = sportsData.find(s => s.name.toLowerCase() === sportName.toLowerCase());
        const sportSlug = sportDataMatch ? sportDataMatch.slug : '';
        const modalSportCategory = document.getElementById('modal-sport-category');
        if (customIconSlugs.includes(sportSlug)) {
            modalSportCategory.innerHTML = `<img src="/images/${sportSlug}-icon.svg" class="w-3.5 h-3.5 object-contain inline" alt="" /> ${sportName} — ${categoryName}`;
        } else {
            modalSportCategory.innerHTML = `<span class="text-sm">${sportIcon}</span> ${sportName} — ${categoryName}`;
        }

        // Match label
        document.getElementById('modal-match-label').textContent = link.dataset.matchLabel || '';

        // Status badge
        const statusEl = document.getElementById('modal-status');
        if (link.dataset.status === 'in_progress') {
            statusEl.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-500 text-white animate-pulse">LIVE</span>';
        } else if (link.dataset.status === 'completed') {
            statusEl.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-500/30 text-blue-300">Final</span>';
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-700 text-slate-400">Upcoming</span>';
        }

        // Period badge
        const periodEl = document.getElementById('modal-period');
        if (link.dataset.currentPeriod) {
            periodEl.textContent = link.dataset.currentPeriod;
            periodEl.classList.remove('hidden');
        } else {
            periodEl.textContent = '';
            periodEl.classList.add('hidden');
        }

        // Team colours & names
        document.getElementById('modal-home-name').textContent = link.dataset.homeName || '';
        document.getElementById('modal-home-color').style.backgroundColor = link.dataset.homeColor || '#94a3b8';
        document.getElementById('modal-away-name').textContent = link.dataset.awayName || '';
        document.getElementById('modal-away-color').style.backgroundColor = link.dataset.awayColor || '#94a3b8';

        // Scores with winner highlight
        const scoreHome = link.dataset.scoreHome;
        const scoreAway = link.dataset.scoreAway;
        const homeScoreEl = document.getElementById('modal-score-home');
        const awayScoreEl = document.getElementById('modal-score-away');
        homeScoreEl.textContent = scoreHome !== '' ? scoreHome : '—';
        awayScoreEl.textContent = scoreAway !== '' ? scoreAway : '—';
        homeScoreEl.classList.remove('text-green-400');
        awayScoreEl.classList.remove('text-green-400');
        if (link.dataset.status === 'completed' && scoreHome !== '' && scoreAway !== '') {
            if (parseInt(scoreHome) > parseInt(scoreAway)) {
                homeScoreEl.classList.add('text-green-400');
            } else if (parseInt(scoreAway) > parseInt(scoreHome)) {
                awayScoreEl.classList.add('text-green-400');
            }
        }

        // Time
        const timeEl = document.getElementById('modal-time');
        if (link.dataset.scheduledAt) {
            timeEl.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + link.dataset.scheduledAt;
            timeEl.classList.remove('hidden');
        } else {
            timeEl.classList.add('hidden');
        }

        // Location
        const locationEl = document.getElementById('modal-location');
        if (link.dataset.location) {
            locationEl.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>' + link.dataset.location;
            locationEl.classList.remove('hidden');
        } else {
            locationEl.classList.add('hidden');
        }

        // Period breakdown
        const breakdownEl = document.getElementById('modal-breakdown');
        const breakdownContentEl = document.getElementById('modal-breakdown-content');
        try {
            const breakdown = JSON.parse(link.dataset.breakdown || '[]');
            const periodLabels = JSON.parse(link.dataset.periodLabels || '[]');
            if (breakdown && breakdown.length > 0) {
                breakdownContentEl.innerHTML = breakdown.map((item, i) =>
                    `<div class="flex flex-col items-center px-2 py-1 rounded-lg bg-white/5 border border-white/10">
                        <span class="text-[10px] text-slate-500 font-medium">${periodLabels[i] || 'P' + (i + 1)}</span>
                        <span class="text-xs font-bold text-slate-200">${item.home ?? 0}-${item.away ?? 0}</span>
                    </div>`
                ).join('');
                breakdownEl.classList.remove('hidden');
            } else {
                breakdownEl.classList.add('hidden');
            }
        } catch(e) {
            breakdownEl.classList.add('hidden');
        }

        document.getElementById('modal-match-link').href = link.dataset.gameUrl || '#';

        // Animate in
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            modalPanel.classList.add('scale-100', 'opacity-100');
            modalPanel.classList.remove('scale-95', 'opacity-0');
        }, 10);
    };

    window.closeGameModal = function() {
        const modal = document.getElementById('game-modal');
        const modalPanel = document.getElementById('game-modal-panel');
        modalPanel.classList.remove('scale-100', 'opacity-100');
        modalPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    };

    window.openGameModalFromData = function(gameId) {
        // Delegate to the unified openGameModal
        openGameModal(gameId);
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeGameModal();
    });

    const backToTopBtn = document.getElementById('back-to-top');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });

    function updateFilterUI() {
        document.querySelectorAll('[data-filter-status]').forEach(btn => {
            const status = btn.getAttribute('data-filter-status');
            if (currentStatuses.includes(status)) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            }
        });
        document.querySelectorAll('[data-filter-sport]').forEach(btn => {
            const sport = btn.getAttribute('data-filter-sport');
            if (currentSports.includes(sport)) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            }
        });
        document.querySelectorAll('[data-filter-color]').forEach(btn => {
            const colorId = btn.getAttribute('data-filter-color');
            if (currentColors.includes(colorId)) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-[#0f172a]', 'text-slate-300', 'hover:bg-[#162033]', 'hover:text-white');
            }
        });
        const clearAllBtn = document.getElementById('clear-all-btn');
        const clearAllDivider = document.getElementById('clear-all-divider');
        const hasFilters = currentSports.length > 0 || currentStatuses.length > 0 || currentColors.length > 0;
        if (hasFilters) {
            clearAllBtn.classList.remove('hidden');
            clearAllDivider.classList.remove('hidden');
        } else {
            clearAllBtn.classList.add('hidden');
            clearAllDivider.classList.add('hidden');
        }
        updateActiveFilters();
    }

    function updateActiveFilters() {
        const container = document.getElementById('active-filters');
        const filterCount = currentStatuses.length + currentSports.length + currentColors.length;
        container.innerHTML = filterCount > 0 ? `${filterCount} applied` : '';
    }

    window.toggleFilter = function(type, value) {
        if (type === 'status') {
            currentStatuses = currentStatuses.includes(value)
                ? currentStatuses.filter(s => s !== value)
                : [...currentStatuses, value];
        } else if (type === 'sport') {
            currentSports = currentSports.includes(value)
                ? currentSports.filter(s => s !== value)
                : [...currentSports, value];
        } else if (type === 'color') {
            currentColors = currentColors.includes(value)
                ? currentColors.filter(c => c !== value)
                : [...currentColors, value];
        }
        fetchFilteredGames();
    };

    window.removeFilter = function(type, value) {
        if (type === 'status') currentStatuses = currentStatuses.filter(s => s !== value);
        else if (type === 'sport') currentSports = currentSports.filter(s => s !== value);
        else if (type === 'color') currentColors = currentColors.filter(c => c !== value);
        fetchFilteredGames();
    };

    window.clearAllFilters = function() {
        currentSports = [];
        currentStatuses = [];
        currentColors = [];
        fetchFilteredGames();
    };

    let fetchTimeout = null;
    function fetchFilteredGames() {
        updateFilterUI();
        if (fetchTimeout) clearTimeout(fetchTimeout);
        fetchTimeout = setTimeout(() => {
            document.getElementById('schedule-loading').classList.remove('hidden');
            document.getElementById('schedule-content').classList.add('hidden');
            const params = new URLSearchParams();
            currentSports.forEach(s => params.append('sport[]', s));
            currentStatuses.forEach(s => params.append('status[]', s));
            currentColors.forEach(c => params.append('color[]', c));
            params.append('start', calendarStart);
            params.append('end', calendarEnd);
            fetch(`/api/schedule?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    renderGames(data.gamesByDate);
                    document.getElementById('schedule-loading').classList.add('hidden');
                    document.getElementById('schedule-content').classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error fetching games:', err);
                    document.getElementById('schedule-loading').classList.add('hidden');
                    document.getElementById('schedule-content').classList.remove('hidden');
                });
        }, 150);
    }

    function renderGames(gamesByDate) {
        const desktopContainer = document.getElementById('desktop-calendar');
        const mobileContainer = document.getElementById('mobile-calendar');
        const emptyState = document.getElementById('empty-state');

        if (!gamesByDate || Object.keys(gamesByDate).length === 0) {
            desktopContainer.innerHTML = '';
            mobileContainer.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }
        if (emptyState) emptyState.classList.add('hidden');

        const now = new Date();
        const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        const dates = [];
        let startDate = new Date(calendarStart + 'T00:00:00');
        const endDate = new Date(calendarEnd + 'T00:00:00');
        while (startDate <= endDate) {
            dates.push(new Date(startDate));
            startDate.setDate(startDate.getDate() + 1);
        }

        const weeks = [];
        for (let i = 0; i < dates.length; i += 7) weeks.push(dates.slice(i, i + 7));

        // ── Desktop ──
        let desktopHtml = `<div class="grid grid-cols-7 mb-2">
            ${dayNames.map(d => `<div class="text-center text-xs font-semibold uppercase tracking-wider text-slate-500 py-2">${d}</div>`).join('')}
        </div>`;
        weeks.forEach(week => {
            desktopHtml += '<div class="grid grid-cols-7 border-t border-white/5">';
            week.forEach(date => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateKey = `${year}-${month}-${day}`;
                const dayGames = gamesByDate[dateKey] || [];
                const isToday = dateKey === today;
                const hasGames = dayGames.length > 0;
                desktopHtml += `<div class="border-r border-b border-white/5 first:border-l min-h-[140px] flex flex-col ${isToday ? 'bg-sky-500/15' : (hasGames ? 'bg-[#1e293b]/50' : 'bg-[#0f172a]/50')}">
                    <div class="px-2 pt-2 pb-1 flex items-center justify-between">
                        <span class="text-sm font-bold ${isToday ? 'text-blue-400' : (hasGames ? 'text-slate-200' : 'text-slate-600')}">
                            ${date.getDate() === 1 || date.getDate() === dates[0].getDate() ? date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : date.getDate()}
                        </span>
                        ${isToday ? '<span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Today</span>' : ''}
                    </div>`;
                if (hasGames) {
                    desktopHtml += '<div class="px-1.5 pb-1.5 flex-1 space-y-0.5 overflow-y-auto">';
                    dayGames.forEach(game => {
                        const statusColor = game.status === 'in_progress' ? 'border-l-green-500 bg-green-500/10' : (game.status === 'completed' ? 'border-l-blue-500/50 bg-blue-500/5' : 'border-l-slate-600 bg-white/[0.03]');
                        const gameTime = game.scheduledTime || new Date(game.scheduledAt).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        const gameTitle = game.eventTitle || (game.teamHome.name + ' vs ' + game.teamAway.name);
                        const gameDisplay = game.eventTitle
                            ? `<span class="text-[10px] text-slate-300 truncate leading-tight font-medium">${game.eventTitle}</span>`
                            : `<span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: ${game.teamHome.colorHex}"></span>
                               <span class="text-[10px] text-slate-400 truncate leading-tight">${game.teamHome.name} <span class="text-slate-600">vs</span> ${game.teamAway.name}</span>
                               <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: ${game.teamAway.colorHex}"></span>`;
                        const gameUrl = `/scores/${game.category.sport.slug}/${game.category.slug}/match-${game.matchParam}`;
                        const breakdown = game.gameData ? (game.gameData.sets || game.gameData.periods || []) : [];
                        const breakdownFiltered = breakdown.filter(item => (item.home || 0) > 0 || (item.away || 0) > 0);
                        desktopHtml += `<a href="${gameUrl}"
                            onclick="event.preventDefault(); openGameModal(${game.id})"
                            data-game-id="${game.id}"
                            data-sport-icon="${game.category.sport.icon || ''}"
                            data-sport-name="${game.category.sport.name || ''}"
                            data-category-name="${game.category.name || ''}"
                            data-match-label="${game.matchLabel || ''}"
                            data-home-name="${game.teamHome.name}"
                            data-home-color="${game.teamHome.colorHex}"
                            data-away-name="${game.teamAway.name}"
                            data-away-color="${game.teamAway.colorHex}"
                            data-score-home="${game.scoreHome ?? ''}"
                            data-score-away="${game.scoreAway ?? ''}"
                            data-status="${game.status}"
                            data-current-period="${game.currentPeriod || ''}"
                            data-scheduled-at="${game.scheduledDateTime || game.scheduledTime || ''}"
                            data-location="${game.location || ''}"
                            data-game-url="${gameUrl}"
                            data-breakdown='${JSON.stringify(breakdownFiltered)}'
                            data-period-labels='${JSON.stringify(game.periodLabels || [])}'
                            class="block border-l-2 ${statusColor} rounded-r px-1.5 py-1 group hover:bg-white/5 transition-colors cursor-pointer"
                            title="${game.category.sport.name} ${game.category.name} — ${gameTitle} @ ${gameTime}${game.location ? ' · ' + game.location : ''}">
                            <div class="flex items-center gap-1 min-w-0">
                                ${customIconSlugs.includes(game.category.sport.slug) ? `<img src="/images/${game.category.sport.slug}-icon.svg" class="w-2.5 h-2.5 object-contain inline" alt="" />` : `<span class="text-[11px] shrink-0">${game.category.sport.icon}</span>`}
                                <span class="text-[11px] text-slate-400 truncate leading-tight">
                                    <span class="font-medium text-slate-300">${gameTime}</span> ${game.category.name}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 mt-0.5 min-w-0">${gameDisplay}</div>
                        </a>`;
                    });
                    desktopHtml += '</div>';
                }
                desktopHtml += '</div>';
            });
            desktopHtml += '</div>';
        });
        desktopContainer.innerHTML = desktopHtml;

        // ── Mobile ──
        let mobileHtml = '';
        weeks.forEach(week => {
            week.forEach(date => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateKey = `${year}-${month}-${day}`;
                const dayGames = gamesByDate[dateKey] || [];
                const isToday = dateKey === today;
                if (!dayGames.length) return;
                const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                const monthDay = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                mobileHtml += `<div class="rounded-2xl border border-white/5 overflow-hidden ${isToday ? 'bg-sky-500/10 border-sky-500/30' : 'bg-[#1e293b]'}">
                    <div class="px-4 py-3 flex items-center gap-3 border-b border-white/5">
                        <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl ${isToday ? 'bg-blue-400 text-slate-900' : 'bg-[#0f172a] text-slate-300'}">
                            <span class="text-[10px] font-semibold uppercase leading-none tracking-wide ${isToday ? 'text-blue-900/70' : 'text-slate-500'}">${date.toLocaleDateString('en-US', { weekday: 'short' })}</span>
                            <span class="text-lg font-bold leading-tight">${date.getDate()}</span>
                        </div>
                        <div>
                            <span class="text-sm font-bold ${isToday ? 'text-blue-400' : 'text-white'}">${dayName}, ${monthDay}</span>
                            ${isToday ? '<span class="ml-2 text-[10px] font-bold uppercase tracking-wider text-blue-400">Today</span>' : ''}
                            <div class="text-xs text-slate-500">${dayGames.length} ${dayGames.length === 1 ? 'game' : 'games'}</div>
                        </div>
                    </div>
                    <div class="divide-y divide-white/5">`;
                dayGames.forEach(game => {
                    const gameTime = game.scheduledTime || new Date(game.scheduledAt).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    const gameUrl = `/scores/${game.category.sport.slug}/${game.category.slug}/match-${game.matchParam}`;
                    const breakdown = game.gameData ? (game.gameData.sets || game.gameData.periods || []) : [];
                    const breakdownFiltered = breakdown.filter(item => (item.home || 0) > 0 || (item.away || 0) > 0);
                    mobileHtml += `<a href="${gameUrl}"
                        onclick="event.preventDefault(); openGameModal(${game.id})"
                        data-game-id="${game.id}"
                        data-sport-icon="${game.category.sport.icon || ''}"
                        data-sport-name="${game.category.sport.name || ''}"
                        data-category-name="${game.category.name || ''}"
                        data-match-label="${game.matchLabel || ''}"
                        data-home-name="${game.teamHome.name}"
                        data-home-color="${game.teamHome.colorHex}"
                        data-away-name="${game.teamAway.name}"
                        data-away-color="${game.teamAway.colorHex}"
                        data-score-home="${game.scoreHome ?? ''}"
                        data-score-away="${game.scoreAway ?? ''}"
                        data-status="${game.status}"
                        data-current-period="${game.currentPeriod || ''}"
                        data-scheduled-at="${game.scheduledDateTime || game.scheduledTime || ''}"
                        data-location="${game.location || ''}"
                        data-game-url="${gameUrl}"
                        data-breakdown='${JSON.stringify(breakdownFiltered)}'
                        data-period-labels='${JSON.stringify(game.periodLabels || [])}'
                        class="block px-4 py-3 hover:bg-white/5 transition-colors cursor-pointer">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 w-14 pt-0.5 text-right">
                                <span class="text-xs font-semibold ${game.status === 'in_progress' ? 'text-green-400' : 'text-slate-400'}">${gameTime}</span>
                            </div>
                            ${game.status === 'in_progress' ? '<div class="shrink-0 mt-1.5"><span class="block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span></div>' : ''}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    ${customIconSlugs.includes(game.category.sport.slug) ? `<img src="/images/${game.category.sport.slug}-icon.svg" class="w-3.5 h-3.5 object-contain inline" alt="" />` : `<span class="text-sm">${game.category.sport.icon}</span>`}
                                    <span class="text-sm font-semibold text-slate-200 truncate">${game.category.sport.name}</span>
                                    <span class="text-xs text-slate-500">&middot;</span>
                                    <span class="text-xs text-slate-400 truncate">${game.category.name}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    ${game.eventTitle
                                        ? `<span class="text-xs font-semibold text-slate-200">${game.eventTitle}</span>`
                                        : `<span class="w-2 h-2 rounded-full shrink-0" style="background-color: ${game.teamHome.colorHex}"></span>
                                           <span class="text-xs font-medium" style="color: ${game.teamHome.colorHex}">${game.teamHome.name}</span>
                                           <span class="text-xs text-slate-600 font-bold">VS</span>
                                           <span class="text-xs font-medium" style="color: ${game.teamAway.colorHex}">${game.teamAway.name}</span>
                                           <span class="w-2 h-2 rounded-full shrink-0" style="background-color: ${game.teamAway.colorHex}"></span>`
                                    }
                                </div>
                                ${game.location ? `<div class="flex items-center gap-1 mt-1 text-[11px] text-slate-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    ${game.location}
                                </div>` : ''}
                                ${game.status === 'in_progress' ? '<div class="mt-1"><span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-500 text-white animate-pulse">LIVE</span></div>' : ''}
                                ${game.status === 'completed' ? '<div class="mt-1"><span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/30 text-blue-300">Final</span></div>' : ''}
                            </div>
                        </div>
                    </a>`;
                });
                mobileHtml += '</div></div>';
            });
        });
        mobileContainer.innerHTML = mobileHtml;
    }

    updateFilterUI();
    const isMobile = window.innerWidth < 768;
    if (isMobile && currentStatuses.length === 0) {
        currentStatuses = ['upcoming', 'live'];
        updateFilterUI();
        fetchFilteredGames();
    } else if (Object.keys(initialGamesByDate).length > 0) {
        renderGames(initialGamesByDate);
    }
})();
</script>
@endpush
@endsection
