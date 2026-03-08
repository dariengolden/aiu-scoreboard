@extends('layouts.app')

@section('title', 'Intramurals 2026')

@section('content')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-[#0c1445] via-[#1e3a8a] to-[#0f172a] overflow-hidden">
    <div class="relative max-w-7xl mx-auto px-4 py-12 md:py-20 text-center">
        <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-4">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">Intramurals</span> 2026
        </h1>
        <p class="text-slate-300 text-lg">Get live scores or check upcoming games here.</p>
    </div>
</section>

{{-- Live games --}}
@if($liveGames->isNotEmpty())
<section class="max-w-7xl mx-auto py-8">
    <div class="flex items-center gap-3 mb-5 px-4">
        <span class="flex items-center gap-2 bg-green-500/20 border border-green-500/40 text-green-400 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            Live Now
        </span>
    </div>
    <div class="flex gap-4 overflow-x-auto overflow-y-visible scroll-smooth snap-x snap-mandatory pb-2 -mb-2" style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch; padding-left: calc((100% - 85vw) / 2); padding-right: calc((100% - 85vw) / 2);">
        @foreach($liveGames as $game)
        <div class="shrink-0 snap-center w-[85vw] max-w-lg">
            <x-game-card :game="$game" />
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Recent results (last 24 hours) --}}
@if($recentResults->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-white">Recent Results <span class="text-slate-500 font-normal text-sm">(last 24 hours)</span></h2>
        <a href="{{ route('schedule') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium">View all &rarr;</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($recentResults as $game)
        <x-game-card :game="$game" />
        @endforeach
    </div>
</section>
@endif

{{-- Upcoming games --}}
@if($upcomingGames->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-white">Upcoming Games</h2>
        <a href="{{ route('schedule') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium">View all &rarr;</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($upcomingGames as $game)
        <x-game-card :game="$game" />
        @endforeach
    </div>
</section>
@endif

{{-- Live polling for home page --}}
@if($liveGames->isNotEmpty())
@php $liveIds = $liveGames->pluck('id')->toArray(); @endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const liveIds = @json($liveIds);
    let pollingInterval = null;

    function startPolling() {
        poll();
        pollingInterval = setInterval(poll, 5000);
    }

    async function poll() {
        try {
            const response = await axios.get('/api/games/batch', {
                params: { ids: liveIds.join(',') }
            });
            const data = response.data;

            Object.keys(data).forEach(gameId => {
                const game = data[gameId];
                const card = document.querySelector(`.game-card[data-game-id="${gameId}"]`);
                if (!card) return;

                const scoreHome = card.querySelector('.game-card-score-home');
                const scoreAway = card.querySelector('.game-card-score-away');
                if (scoreHome) scoreHome.textContent = game.score_home ?? '—';
                if (scoreAway) scoreAway.textContent = game.score_away ?? '—';

                const periodEl = card.querySelector('.game-card-period');
                if (game.current_period && periodEl) {
                    periodEl.textContent = game.current_period;
                }

                // Update breakdown - compact badges
                const breakdownEl = card.querySelector('.game-card-breakdown');
                if (game.game_data) {
                    const isSets = !!game.game_data.sets;
                    const dataKey = isSets ? 'sets' : 'periods';
                    const items = game.game_data[dataKey] || [];
                    const itemsWithScores = items.filter(item => (item.home || 0) > 0 || (item.away || 0) > 0);

                    if (itemsWithScores.length > 0) {
                        let html = '<div class="flex items-center gap-1.5 text-xs tabular-nums">';
                        itemsWithScores.forEach(item => {
                            html += `<span class="px-1.5 py-0.5 rounded bg-white/5 text-slate-400 font-medium">${item.home || 0}-${item.away || 0}</span>`;
                        });
                        html += '</div>';
                        if (breakdownEl) {
                            breakdownEl.innerHTML = html;
                        } else {
                            const scoresDiv = card.querySelector('.px-4.pb-3');
                            if (scoresDiv) {
                                const div = document.createElement('div');
                                div.className = 'game-card-breakdown mt-2 pt-2 border-t border-white/5';
                                div.innerHTML = html;
                                scoresDiv.appendChild(div);
                            }
                        }
                    } else if (breakdownEl) {
                        breakdownEl.innerHTML = '';
                    }
                }
            });
        } catch (err) {
            console.error('Polling error:', err);
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
