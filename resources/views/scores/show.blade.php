@extends('layouts.app')

@section('title', $sport->name)

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('scores.index') }}" class="hover:text-white transition-colors">Scores</a>
        <span>/</span>
        <span class="text-white font-medium">{{ $sport->name }}</span>
    </div>

    {{-- Sport header --}}
    <div class="flex items-center gap-4 mb-6">
        <span class="text-5xl">{{ $sport->icon }}</span>
        <div>
            <h1 class="text-3xl font-black text-white">{{ $sport->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $categories->count() }} {{ Str::plural('category', $categories->count()) }} &middot; Round Robin</p>
        </div>
    </div>

    {{-- Category filter --}}
    @if($categories->count() > 1)
    <div class="flex flex-wrap items-center gap-2 mb-8">
        <a href="{{ route('scores.show', $sport) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all {{ !$selectedCategory ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('scores.show', ['sport' => $sport, 'category' => $cat->slug]) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all {{ $selectedCategory === $cat->slug ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Categories --}}
    <div>
        @foreach($visibleCategories as $category)
        <div class="{{ !$loop->first ? 'pt-16' : '' }}">
            <h2 class="text-xl font-bold text-white mb-4">{{ $category->name }}</h2>

            @php
                $categoryGames = $games[$category->id] ?? collect();
                $completed = $categoryGames->where('status', 'completed')->count();
                $total = $categoryGames->count();
                $live = $categoryGames->where('status', 'in_progress')->count();
                $standings = $standingsByCategory[$category->id] ?? [];
            @endphp

            {{-- Standings table --}}
            <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Standings</h3>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400">{{ $total }} matches</span>
                        <span class="text-green-400 font-medium">{{ $completed }} completed</span>
                        @if($live > 0)
                        <span class="text-green-400 font-medium flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                            {{ $live }} live
                        </span>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                                <th class="text-left px-4 py-3 w-8">#</th>
                                <th class="text-left px-4 py-3">Team</th>
                                <th class="text-center px-2 py-3" title="Played"><abbr title="Played" class="no-underline cursor-help">P</abbr></th>
                                <th class="text-center px-2 py-3" title="Wins"><abbr title="Wins" class="no-underline cursor-help">W</abbr></th>
                                <th class="text-center px-2 py-3" title="Draws"><abbr title="Draws" class="no-underline cursor-help">D</abbr></th>
                                <th class="text-center px-2 py-3" title="Losses"><abbr title="Losses" class="no-underline cursor-help">L</abbr></th>
                                <th class="text-center px-2 py-3 hidden sm:table-cell" title="Goals For"><abbr title="Goals For" class="no-underline cursor-help">GF</abbr></th>
                                <th class="text-center px-2 py-3 hidden sm:table-cell" title="Goals Against"><abbr title="Goals Against" class="no-underline cursor-help">GA</abbr></th>
                                <th class="text-center px-2 py-3" title="Goal Difference"><abbr title="Goal Difference" class="no-underline cursor-help">GD</abbr></th>
                                <th class="text-center px-4 py-3 font-bold" title="Points"><abbr title="Points" class="no-underline cursor-help">PTS</abbr></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standings as $index => $row)
                            <tr class="border-b border-white/5 last:border-b-0 {{ $index === 0 && $row['played'] > 0 ? 'bg-yellow-500/5' : '' }}">
                                <td class="px-4 py-3 text-slate-500 font-bold">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $row['team']->color_hex }}"></span>
                                        <span class="font-semibold text-white">{{ $row['team']->name }}</span>
                                        @if($index === 0 && $row['played'] > 0 && $row['points'] > 0)
                                        <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center px-2 py-3 text-slate-300">{{ $row['played'] }}</td>
                                <td class="text-center px-2 py-3 text-green-400 font-medium">{{ $row['won'] }}</td>
                                <td class="text-center px-2 py-3 text-slate-400">{{ $row['drawn'] }}</td>
                                <td class="text-center px-2 py-3 text-red-400">{{ $row['lost'] }}</td>
                                <td class="text-center px-2 py-3 text-slate-400 hidden sm:table-cell">{{ $row['goals_for'] }}</td>
                                <td class="text-center px-2 py-3 text-slate-400 hidden sm:table-cell">{{ $row['goals_against'] }}</td>
                                <td class="text-center px-2 py-3 font-medium {{ $row['goal_difference'] > 0 ? 'text-green-400' : ($row['goal_difference'] < 0 ? 'text-red-400' : 'text-slate-400') }}">
                                    {{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}
                                </td>
                                <td class="text-center px-4 py-3 font-black text-white text-base">{{ $row['points'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Matches --}}
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Matches</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($categoryGames->sortBy('match_number') as $game)
                <x-game-card :game="$game" />
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Live polling script --}}
@php
    $allGames = $visibleCategories->flatMap(fn ($cat) => $games[$cat->id] ?? []);
    $liveGameIds = $allGames->where('status', 'in_progress')->pluck('id')->toArray();
    $allGameIds = $allGames->pluck('id')->toArray();
@endphp

@if(count($allGameIds) > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const allGameIds = @json($allGameIds);
    let pollingInterval = null;
    let lastData = {};

    function startPolling() {
        poll(); // Immediate first poll
        pollingInterval = setInterval(poll, 5000); // Every 5 seconds
    }

    async function poll() {
        try {
            const response = await axios.get('/api/games/batch', {
                params: { ids: allGameIds.join(',') }
            });

            const data = response.data;

            Object.keys(data).forEach(gameId => {
                const game = data[gameId];
                const card = document.querySelector(`.game-card[data-game-id="${gameId}"]`);
                if (!card) return;

                // Check if data actually changed
                const lastJson = JSON.stringify(lastData[gameId] || {});
                const newJson = JSON.stringify(game);
                if (lastJson === newJson) return;
                lastData[gameId] = game;

                // Update scores
                const scoreHome = card.querySelector('.game-card-score-home');
                const scoreAway = card.querySelector('.game-card-score-away');
                if (scoreHome) scoreHome.textContent = game.score_home ?? '—';
                if (scoreAway) scoreAway.textContent = game.score_away ?? '—';

                // Update period display
                const periodEl = card.querySelector('.game-card-period');
                if (game.current_period) {
                    if (periodEl) {
                        periodEl.textContent = game.current_period;
                    } else {
                        // Create period element if it doesn't exist
                        const headerRight = card.querySelector('.text-xs.font-semibold.text-slate-500');
                        if (headerRight && headerRight.parentNode) {
                            const span = document.createElement('span');
                            span.className = 'game-card-period text-xs font-bold text-blue-400';
                            span.textContent = game.current_period;
                            headerRight.parentNode.appendChild(span);
                        }
                    }
                }

                // Update status badge
                const statusBadge = card.querySelector('[class*="status-badge"]') || card.querySelector('.inline-flex');
                // Flash the card border briefly for live games
                if (game.status === 'in_progress') {
                    card.dataset.isLive = '1';
                    if (!card.classList.contains('border-green-500/20')) {
                        card.classList.add('border-green-500/20');
                    }
                } else {
                    card.dataset.isLive = '0';
                    card.classList.remove('border-green-500/20');
                }

                // Update period/set breakdown
                const breakdownEl = card.querySelector('.game-card-breakdown');
                if (game.game_data) {
                    const dataKey = (game.game_data.sets) ? 'sets' : 'periods';
                    const items = game.game_data[dataKey] || [];
                    const hasData = items.some(item => (item.home || 0) > 0 || (item.away || 0) > 0);

                    if (hasData) {
                        let html = '<div class="flex items-center gap-1.5 text-xs tabular-nums">';
                        items.forEach(item => {
                            if ((item.home || 0) > 0 || (item.away || 0) > 0) {
                                html += `<span class="px-1.5 py-0.5 rounded bg-white/5 text-slate-400 font-medium">${item.home || 0}-${item.away || 0}</span>`;
                            }
                        });
                        html += '</div>';

                        if (breakdownEl) {
                            breakdownEl.innerHTML = html;
                        } else {
                            // Insert breakdown before draw indicator or at end of scores section
                            const scoresDiv = card.querySelector('.px-4.pb-3');
                            if (scoresDiv) {
                                const div = document.createElement('div');
                                div.className = 'game-card-breakdown mt-2 pt-2 border-t border-white/5';
                                div.innerHTML = html;
                                scoresDiv.appendChild(div);
                            }
                        }
                    }
                }
            });
        } catch (err) {
            console.error('Polling error:', err);
        }
    }

    // Start polling
    startPolling();

    // Pause when tab hidden, resume when visible
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
