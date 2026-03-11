@extends('layouts.app')

@section('title', $game->match_label)

@section('content')

@php
    $sport = $game->category?->sport;
    $category = $game->category;
    $homeTeam = $game->teamHome;
    $awayTeam = $game->teamAway;
    $config = $game->sport_config;
    $type = $config['type'] ?? null;
    $disciplineConfig = $config['discipline'] ?? null;
    $gameData = $game->game_data ?? [];
    $labels = $game->period_labels;
    $isCeremony = !empty($game->event_type);
    $isRunning = $type === 'places';
    $places = $gameData['places'] ?? [];

    function getTeamShow($id) {
        return $id ? \App\Models\Team::find($id) : null;
    }

    $rows = [];
    if ($type === 'sets') {
        $rows = $gameData['sets'] ?? [];
    } elseif (in_array($type, ['quarters', 'halves'])) {
        $rows = $gameData['periods'] ?? [];
    }

    $hasBreakdown = collect($rows)->contains(fn($item) => ($item['home'] ?? 0) > 0 || ($item['away'] ?? 0) > 0);
    $showBreakdownSection = $hasBreakdown || ($game->isLive() && in_array($type, ['sets', 'quarters', 'halves']));

    $showYellow = $disciplineConfig['yellow'] ?? false;
    $showRed = $disciplineConfig['red'] ?? false;

    // Only treat cards as "discipline section" content; fouls are not shown.
    $hasCardDisciplineData =
        ($showYellow && (($game->yellow_cards_home ?? 0) > 0 || ($game->yellow_cards_away ?? 0) > 0)) ||
        ($showRed && (($game->red_cards_home ?? 0) > 0 || ($game->red_cards_away ?? 0) > 0));
@endphp

<div class="max-w-5xl mx-auto px-4 py-6" data-game-id="{{ $game->id }}" data-live="{{ $game->isLive() ? '1' : '0' }}" data-period-labels='@json($labels)' data-type="{{ $type }}" data-home-color="{{ $homeTeam?->color_hex ?? '#6b7280' }}" data-away-color="{{ $awayTeam?->color_hex ?? '#6b7280' }}" data-home-name="{{ $homeTeam?->name ?? '' }}" data-away-name="{{ $awayTeam?->name ?? '' }}">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('scores.index') }}" class="hover:text-white transition-colors">Scores</a>
        @if($sport)
            <span>/</span>
            <a href="{{ route('scores.show', ['sport' => $sport, 'category' => $category?->slug]) }}"
               class="hover:text-white transition-colors">
                {{ $sport->name }}@if($category) - {{ $category->name }}@endif
            </a>
        @endif
        <span>/</span>
        <span class="text-white font-medium">{{ $game->match_label }}</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            @if($sport)
                <span class="text-4xl md:text-5xl">{{ $sport->icon }}</span>
            @endif
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-white">
                    @if($isRunning)
                        {{ $category->name }} &mdash; Places
                    @else
                        {{ $homeTeam?->name ?? '—' }} vs {{ $awayTeam?->name ?? '—' }}
                    @endif
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    {{ $sport?->name }} @if($category) &middot; {{ $category->name }} @endif
                </p>
            </div>
        </div>
        <div class="flex flex-col items-start md:items-end gap-2">
            @auth
                <a href="{{ route('games.edit', $game) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Match
                </a>
            @endauth
            @if($game->scheduled_at)
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $game->scheduled_at->format('D, M j · g:ia') }}</span>
                </div>
            @endif
            @if($game->location)
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span>{{ $game->location }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Main content --}}
    <div class="space-y-6">

        @if($isCeremony)
        {{-- Ceremony: event info only, no scores --}}
        <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
            <div class="px-6 py-8 text-center">
                <h2 class="text-2xl font-black text-white mb-2">{{ $game->event_title ?? 'Ceremony' }}</h2>
                @if($game->scheduled_at)
                <p class="text-slate-400 text-sm">
                    {{ $game->scheduled_at->format('l, F j, Y \a\t g:ia') }}
                    @if($game->scheduled_end_at)
                        – {{ $game->scheduled_end_at->format('g:ia') }}
                    @endif
                </p>
                @endif
                @if($game->location)
                <p class="text-slate-400 text-sm mt-1">{{ $game->location }}</p>
                @endif
            </div>
        </div>
        @else
        {{-- Scoreboard --}}
        <div class="space-y-4">
            <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
                <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        {{ $game->match_label }}
                        <span class="game-current-period ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 text-[11px] font-bold {{ !$game->current_period ? 'hidden' : '' }}">{{ $game->current_period ?? '' }}</span>
                    </div>
                    @if($game->isLive())
                        <span class="game-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Live
                        </span>
                    @elseif($game->isCompleted())
                        <span class="game-status-badge inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 text-[11px] font-bold uppercase tracking-wider">
                            Final
                        </span>
                    @endif
                </div>

                <div class="px-6 py-6">
                    {{-- Teams + score (or Places for Running) --}}
                    <div class="space-y-3 mb-2">
                        @if($isRunning)
                            {{-- Running: Show places 1st-4th --}}
                            @for($i = 1; $i <= 4; $i++)
                            @php $placeTeam = getTeamShow($places[$i] ?? null); @endphp
                            <div class="flex items-center justify-between py-2 px-4 bg-white/5 rounded-xl">
                                <div class="flex items-center gap-3">
                                    @if($i === 1)
                                        <span class="text-xl">🥇</span>
                                    @elseif($i === 2)
                                        <span class="text-xl">🥈</span>
                                    @elseif($i === 3)
                                        <span class="text-xl">🥉</span>
                                    @else
                                        <span class="text-sm font-bold text-slate-500 w-6">4th</span>
                                    @endif
                                    <span class="font-semibold text-base {{ $placeTeam ? 'text-white' : 'text-slate-500' }}">
                                        {{ $placeTeam?->name ?? '—' }}
                                    </span>
                                </div>
                            </div>
                            @endfor
                        @else
                        {{-- Regular: Team vs Team --}}
                        {{-- Team names row --}}
                        <div class="flex items-center justify-between gap-4">
                            {{-- Home --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $homeTeam?->color_hex ?? '#6b7280' }}"></span>
                                <span class="font-semibold text-base md:text-lg truncate {{ $game->winner_id === $homeTeam?->id ? 'text-white' : 'text-slate-300' }}">
                                    {{ $homeTeam?->name ?? '—' }}
                                </span>
                                @if($game->winner_id === $homeTeam?->id)
                                    <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Away --}}
                            <div class="flex items-center gap-3 min-w-0 justify-end">
                                @if($game->winner_id === $awayTeam?->id)
                                    <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                                <span class="font-semibold text-base md:text-lg truncate text-right {{ $game->winner_id === $awayTeam?->id ? 'text-white' : 'text-slate-300' }}">
                                    {{ $awayTeam?->name ?? '—' }}
                                </span>
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $awayTeam?->color_hex ?? '#6b7280' }}"></span>
                            </div>
                        </div>

                        {{-- Scores row --}}
                        <div class="flex items-center justify-between gap-4">
                            <div class="game-score-home text-3xl md:text-4xl font-black tabular-nums {{ $game->winner_id === $homeTeam?->id ? 'text-white' : 'text-slate-300' }}">
                                {{ $game->score_home ?? '—' }}
                            </div>

                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                vs
                            </span>

                            <div class="game-score-away text-3xl md:text-4xl font-black tabular-nums text-right {{ $game->winner_id === $awayTeam?->id ? 'text-white' : 'text-slate-300' }}">
                                {{ $game->score_away ?? '—' }}
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Draw indicator --}}
                    @if(!$isRunning && $game->isCompleted() && $game->score_home !== null && $game->score_home === $game->score_away)
                        <div class="mt-2 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Draw
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Period / Set breakdown --}}
            @if($showBreakdownSection)
                <div class="bg-[#0f172a] rounded-2xl border border-white/5 overflow-hidden">
                    <div class="px-4 pt-4 flex items-center justify-between gap-2">
                        <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider">
                            @if($type === 'sets')
                                Score by set
                            @elseif($type === 'quarters')
                                Score by quarter
                            @elseif($type === 'halves')
                                Score by half
                            @else
                                Score breakdown
                            @endif
                        </h2>
                    </div>

                    <div class="px-4 pt-5 pb-4 space-y-4">
                        {{-- Discipline: cards only --}}
                        @if($disciplineConfig && $hasCardDisciplineData)
                            <div class="mt-4">
                                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Discipline</h3>
                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $homeTeam->color_hex }}"></span>
                                            <span class="font-semibold text-slate-200">{{ $homeTeam->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($showYellow)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-300">
                                                    <span class="w-2 h-3 bg-yellow-400 rounded-sm"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->yellow_cards_home ?? 0 }}</span>
                                                </span>
                                            @endif
                                            @if($showRed)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/10 text-red-300">
                                                    <span class="w-2 h-3 bg-red-500 rounded-sm"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->red_cards_home ?? 0 }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $awayTeam->color_hex }}"></span>
                                            <span class="font-semibold text-slate-200">{{ $awayTeam->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($showYellow)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-300">
                                                    <span class="w-2 h-3 bg-yellow-400 rounded-sm"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->yellow_cards_away ?? 0 }}</span>
                                                </span>
                                            @endif
                                            @if($showRed)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/10 text-red-300">
                                                    <span class="w-2 h-3 bg-red-500 rounded-sm"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->red_cards_away ?? 0 }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div id="game-breakdown-rows" class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-white/5 text-slate-500 uppercase tracking-wider">
                                        <th class="text-left py-2 pr-2">Team</th>
                                        @foreach($rows as $i => $row)
                                            @if(($row['home'] ?? 0) > 0 || ($row['away'] ?? 0) > 0)
                                                <th class="text-center py-2 px-1">{{ $labels[$i] ?? 'P'.($i + 1) }}</th>
                                            @endif
                                        @endforeach
                                        <th class="text-center py-2 pl-2 font-bold text-white">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-white/5">
                                        <td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:{{ $homeTeam->color_hex }}"></span><span class="font-semibold text-white">{{ $homeTeam->name }}</span></span></td>
                                        @foreach($rows as $i => $row)
                                            @if(($row['home'] ?? 0) > 0 || ($row['away'] ?? 0) > 0)
                                                <td class="text-center py-2 px-1 font-bold tabular-nums text-slate-300">{{ $row['home'] ?? 0 }}</td>
                                            @endif
                                        @endforeach
                                        <td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">{{ $game->score_home ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:{{ $awayTeam->color_hex }}"></span><span class="font-semibold text-white">{{ $awayTeam->name }}</span></span></td>
                                        @foreach($rows as $i => $row)
                                            @if(($row['home'] ?? 0) > 0 || ($row['away'] ?? 0) > 0)
                                                <td class="text-center py-2 px-1 font-bold tabular-nums text-slate-300">{{ $row['away'] ?? 0 }}</td>
                                            @endif
                                        @endforeach
                                        <td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">{{ $game->score_away ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@if($game->isLive() && !$isCeremony)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('[data-game-id]');
    if (!container || container.dataset.live !== '1') return;

    const gameId = container.dataset.gameId;
    const periodLabels = JSON.parse(container.dataset.periodLabels || '[]');
    const type = container.dataset.type;
    const homeColor = container.dataset.homeColor || '#94a3b8';
    const awayColor = container.dataset.awayColor || '#94a3b8';
    const homeName = container.dataset.homeName || 'Home';
    const awayName = container.dataset.awayName || 'Away';

    let pollingInterval = null;

    function startPolling() {
        poll();
        pollingInterval = setInterval(poll, 5000);
    }

    async function poll() {
        try {
            const response = await axios.get('/api/games/batch', {
                params: { ids: gameId }
            });
            const game = response.data[gameId];
            if (!game) return;

            const scoreHomeEl = container.querySelector('.game-score-home');
            const scoreAwayEl = container.querySelector('.game-score-away');
            if (scoreHomeEl) scoreHomeEl.textContent = game.score_home ?? '—';
            if (scoreAwayEl) scoreAwayEl.textContent = game.score_away ?? '—';

            const periodEl = container.querySelector('.game-current-period');
            if (periodEl) {
                if (game.current_period) {
                    periodEl.textContent = game.current_period;
                    periodEl.classList.remove('hidden');
                } else {
                    periodEl.textContent = '';
                    periodEl.classList.add('hidden');
                }
            }

            const statusBadge = container.querySelector('.game-status-badge');
            if (statusBadge) {
                if (game.status === 'in_progress') {
                    statusBadge.className = 'game-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider';
                    statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> Live';
                } else if (game.status === 'completed') {
                    statusBadge.className = 'game-status-badge inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 text-[11px] font-bold uppercase tracking-wider';
                    statusBadge.textContent = 'Final';
                }
            }

            const breakdownRows = document.getElementById('game-breakdown-rows');
            if (breakdownRows && game.game_data) {
                const isSets = !!game.game_data.sets;
                const dataKey = isSets ? 'sets' : 'periods';
                const items = game.game_data[dataKey] || [];
                const itemsWithScores = items.filter(item => (item.home || 0) > 0 || (item.away || 0) > 0);

                let html = '<table class="w-full text-xs"><thead><tr class="border-b border-white/5 text-slate-500 uppercase tracking-wider">';
                html += '<th class="text-left py-2 pr-2">Team</th>';
                itemsWithScores.forEach((item, i) => {
                    html += `<th class="text-center py-2 px-1">${periodLabels[i] || 'P' + (i + 1)}</th>`;
                });
                html += '<th class="text-center py-2 pl-2 font-bold text-white">Total</th></tr></thead><tbody>';
                html += `<tr class="border-b border-white/5"><td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:${homeColor}"></span><span class="font-semibold text-white">${homeName}</span></span></td>`;
                itemsWithScores.forEach(item => {
                    html += `<td class="text-center py-2 px-1 font-bold tabular-nums text-slate-300">${item.home || 0}</td>`;
                });
                html += `<td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">${game.score_home ?? 0}</td></tr>`;
                html += `<tr><td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:${awayColor}"></span><span class="font-semibold text-white">${awayName}</span></span></td>`;
                itemsWithScores.forEach(item => {
                    html += `<td class="text-center py-2 px-1 font-bold tabular-nums text-slate-300">${item.away || 0}</td>`;
                });
                html += `<td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">${game.score_away ?? 0}</td></tr>`;
                html += '</tbody></table>';
                breakdownRows.innerHTML = html;
            }
        } catch (err) {
            console.error('Live score polling error:', err);
        }
    }

    startPolling();
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else {
            startPolling();
        }
    });
});
</script>
@endif

@endsection
