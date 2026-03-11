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
                <span id="active-filters">
                @foreach($selectedStatuses as $key)
                    <a href="{{ scheduleRemoveUrl($selectedSports, $selectedStatuses, removeStatus: $key) }}"
                       data-filter-status-remove="{{ $key }}"
                       onclick="event.preventDefault(); removeFilter('status', '{{ $key }}')"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                        {{ $statusOptions[$key] ?? $key }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endforeach
                @foreach($selectedSports as $slug)
                    @php $sportObj = $sports->firstWhere('slug', $slug); @endphp
                    @if($sportObj)
                    <a href="{{ scheduleRemoveUrl($selectedSports, $selectedStatuses, removeSport: $slug) }}"
                       data-filter-sport-remove="{{ $slug }}"
                       onclick="event.preventDefault(); removeFilter('sport', '{{ $slug }}')"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                        {{ $sportObj->icon }} {{ $sportObj->name }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                @endforeach
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
                            {{ $sport->icon }} {{ $sport->name }}
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
                        $gameTitle = $game->event_title ?: $game->teamHome->name . ' vs ' . $game->teamAway->name;
                    @endphp
                    <a href="{{ route('games.show', ['sport' => $game->category->sport->slug, 'category' => $game->category->slug, 'match' => $game->match_number ?? $game->id]) }}" class="block border-l-2 {{ $statusColor }} rounded-r px-1.5 py-1 group hover:bg-white/5 transition-colors" title="{{ $game->category->sport->name }} {{ $game->category->name }} — {{ $gameTitle }} @ {{ $game->scheduled_at->format('g:ia') }}{{ $game->location ? ' · ' . $game->location : '' }}">
                        <div class="flex items-center gap-1 min-w-0">
                            <span class="text-[11px] shrink-0">{{ $game->category->sport->icon }}</span>
                            <span class="text-[11px] text-slate-400 truncate leading-tight">
                                <span class="font-medium text-slate-300">{{ $game->scheduled_at->format('g:ia') }}</span>
                                {{ $game->category->name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5 min-w-0">
                            @if($game->event_title)
                            <span class="text-[10px] text-slate-300 truncate leading-tight font-medium">{{ $game->event_title }}</span>
                            @else
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                            <span class="text-[10px] text-slate-400 truncate leading-tight">
                                {{ $game->teamHome->name }}
                                <span class="text-slate-600">vs</span>
                                {{ $game->teamAway->name }}
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $game->teamAway->color_hex }}"></span>
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
                    <a href="{{ route('games.show', ['sport' => $game->category->sport->slug, 'category' => $game->category->slug, 'match' => $game->match_number ?? $game->id]) }}" class="block px-4 py-3 hover:bg-white/5 transition-colors">
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
                                    @if($game->event_title)
                                    <span class="text-xs font-semibold text-slate-200">{{ $game->event_title }}</span>
                                    @else
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamHome->color_hex }}">{{ $game->teamHome->name }}</span>
                                    <span class="text-xs text-slate-600 font-bold">VS</span>
                                    <span class="text-xs font-medium" style="color: {{ $game->teamAway->color_hex }}">{{ $game->teamAway->name }}</span>
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $game->teamAway->color_hex }}"></span>
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

@push('scripts')
<script>
(function() {
    let currentSports = @json($selectedSports ?? []);
    let currentStatuses = @json($selectedStatuses ?? []);
    let currentColors = @json($selectedColors ?? []);
    const calendarStart = '{{ $calendarStart->format("Y-m-d") }}';
    const calendarEnd = '{{ $calendarEnd->format("Y-m-d") }}';
    const sportsData = @json($sports->map(fn($s) => ['slug' => $s->slug, 'name' => $s->name, 'icon' => $s->icon]));
    const teamsData = @json($teams->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'colorHex' => $t->color_hex]));
    const initialGamesByDate = @json($gamesByDateResource ?? []);

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
        let html = '';

        const statusLabels = { upcoming: 'Upcoming', live: 'Live', past: 'Past' };

        currentStatuses.forEach(status => {
            const label = statusLabels[status] || status;
            html += `<a href="#" data-filter-status-remove="${status}" onclick="event.preventDefault(); removeFilter('status', '${status}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                ${label}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>`;
        });

        currentSports.forEach(slug => {
            const sport = sportsData.find(s => s.slug === slug);
            if (sport) {
                html += `<a href="#" data-filter-sport-remove="${slug}" onclick="event.preventDefault(); removeFilter('sport', '${slug}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                    ${sport.icon} ${sport.name}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>`;
            }
        });

        currentColors.forEach(colorId => {
            const team = teamsData.find(t => t.id == colorId);
            if (team) {
                html += `<a href="#" data-filter-color-remove="${colorId}" onclick="event.preventDefault(); removeFilter('color', '${colorId}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 text-xs font-medium hover:bg-blue-600/25 transition-colors">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${team.colorHex}"></span>
                    ${team.name}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>`;
            }
        });

        container.innerHTML = html;
    }

    window.toggleFilter = function(type, value) {
        if (type === 'status') {
            if (currentStatuses.includes(value)) {
                currentStatuses = currentStatuses.filter(s => s !== value);
            } else {
                currentStatuses.push(value);
            }
        } else if (type === 'sport') {
            if (currentSports.includes(value)) {
                currentSports = currentSports.filter(s => s !== value);
            } else {
                currentSports.push(value);
            }
        } else if (type === 'color') {
            if (currentColors.includes(value)) {
                currentColors = currentColors.filter(c => c !== value);
            } else {
                currentColors.push(value);
            }
        }
        fetchFilteredGames();
    };

    window.removeFilter = function(type, value) {
        if (type === 'status') {
            currentStatuses = currentStatuses.filter(s => s !== value);
        } else if (type === 'sport') {
            currentSports = currentSports.filter(s => s !== value);
        } else if (type === 'color') {
            currentColors = currentColors.filter(c => c !== value);
        }
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
        console.log('Rendering games with calendarStart:', calendarStart, 'calendarEnd:', calendarEnd);
        console.log('gamesByDate keys:', gamesByDate ? Object.keys(gamesByDate) : 'none');

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
        const todayYear = now.getFullYear();
        const todayMonth = String(now.getMonth() + 1).padStart(2, '0');
        const todayDay = String(now.getDate()).padStart(2, '0');
        const today = `${todayYear}-${todayMonth}-${todayDay}`;
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        console.log('Building dates from:', calendarStart, 'to', calendarEnd);

        if (!calendarStart || !calendarEnd) {
            console.error('Invalid calendar dates!');
            return;
        }

        const dates = [];
        let startDate = new Date(calendarStart + 'T00:00:00');
        const endDate = new Date(calendarEnd + 'T00:00:00');

        console.log('Parsed dates - start:', startDate, 'end:', endDate, 'valid:', startDate instanceof Date && !isNaN(startDate), endDate instanceof Date && !isNaN(endDate));
        while (startDate <= endDate) {
            dates.push(new Date(startDate));
            startDate.setDate(startDate.getDate() + 1);
        }

        const weeks = [];
        for (let i = 0; i < dates.length; i += 7) {
            weeks.push(dates.slice(i, i + 7));
        }

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
                                <span class="text-[10px] text-slate-400 truncate leading-tight">
                                    ${game.teamHome.name}
                                    <span class="text-slate-600">vs</span>
                                    ${game.teamAway.name}
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: ${game.teamAway.colorHex}"></span>`;
                        desktopHtml += `<a href="/scores/${game.category.sport.slug}/${game.category.slug}/match-${game.matchParam}" class="block border-l-2 ${statusColor} rounded-r px-1.5 py-1 group hover:bg-white/5 transition-colors" title="${game.category.sport.name} ${game.category.name} — ${gameTitle} @ ${gameTime}${game.location ? ' · ' + game.location : ''}">
                            <div class="flex items-center gap-1 min-w-0">
                                <span class="text-[11px] shrink-0">${game.category.sport.icon}</span>
                                <span class="text-[11px] text-slate-400 truncate leading-tight">
                                    <span class="font-medium text-slate-300">${gameTime}</span>
                                    ${game.category.name}
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

        let mobileHtml = '';
        weeks.forEach(week => {
            week.forEach(date => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateKey = `${year}-${month}-${day}`;
                const dayGames = gamesByDate[dateKey] || [];
                const isToday = dateKey === today;
                const hasGames = dayGames.length > 0;

                if (hasGames) {
                    const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                    const monthDay = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

                    mobileHtml += `<div class="rounded-2xl border border-white/5 overflow-hidden ${isToday ? 'bg-sky-500/10 border-sky-500/30' : 'bg-[#1e293b]'}">
                        <div class="px-4 py-3 flex items-center justify-between border-b border-white/5">
                            <div class="flex items-center gap-3">
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
                        </div>
                        <div class="divide-y divide-white/5">`;

                    dayGames.forEach(game => {
                        const gameTime = game.scheduledTime || new Date(game.scheduledAt).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        mobileHtml += `<a href="/scores/${game.category.sport.slug}/${game.category.slug}/match-${game.matchParam}" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-14 pt-0.5 text-right">
                                    <span class="text-xs font-semibold ${game.status === 'in_progress' ? 'text-green-400' : 'text-slate-400'}">
                                        ${gameTime}
                                    </span>
                                </div>
                                ${game.status === 'in_progress' ? '<div class="shrink-0 mt-1.5"><span class="block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span></div>' : ''}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm">${game.category.sport.icon}</span>
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
                }
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
