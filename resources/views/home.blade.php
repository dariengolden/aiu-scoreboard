@extends('layouts.app')

@section('title', 'Scoreboard')

@section('content')

@if($announcement)
<x-announcement-modal :title="$announcement->title">
    {!! $announcement->content !!}
</x-announcement-modal>
@endif

{{-- Hero --}}
<section class="hero-section relative bg-gradient-to-br from-[#0c1445] via-[#1e3a8a] to-[#0f172a] overflow-hidden min-h-[230px] md:min-h-[400px]">
    <div class="absolute inset-0 z-0">
        @for($i = 1; $i <= 4; $i++)
        <img
            src="/images/hero-0{{ $i }}.webp"
            alt=""
            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out hero-slide opacity-0"
            data-slide="{{ $i }}"
            loading="eager"
            fetchpriority="high"
        >
        @endfor
        <div class="absolute inset-0 bg-black/50"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-8 md:py-20 flex flex-col items-center justify-center text-center min-h-[230px] md:min-h-[400px]">
        <h1 class="text-3xl md:text-6xl font-black text-white leading-tight mb-4 drop-shadow-lg">
            <span>Official Scoreboard</span>
        </h1>
        <p class="text-slate-300 text-base md:text-lg drop-shadow-md">Get live scores or check upcoming games for the 2026 AIU Intramurals.</p>
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
        <h2 class="text-lg font-bold text-white">Recent<span class="text-slate-500 font-normal text-sm"></span></h2>
        <a href="{{ route('scores.index') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium">View all &rarr;</a>
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
        <h2 class="text-lg font-bold text-white">Upcoming</h2>
        <a href="{{ route('schedule') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium">View all &rarr;</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($upcomingGames as $game)
        <x-game-card :game="$game" />
        @endforeach
    </div>
</section>
@endif

{{-- Final Standings --}}
@if(!empty($overallStandings))
@php
    $maxPoints = collect($overallStandings)->max('points') ?: 1;
    $medals = ['🥇', '🥈', '🥉'];
@endphp
<section class="max-w-7xl mx-auto px-4 py-8 pb-4">
    {{-- Section header --}}
    <div class="relative mb-8 text-center">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-white/5"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="bg-[#0f172a] px-6 py-1">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-500">
                    <svg class="w-4 h-4 text-yellow-500/70" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Final Standings
                </span>
            </span>
        </div>
    </div>

    {{-- Standings cards --}}
    <div class="space-y-3" id="standings-list">
        @foreach($overallStandings as $i => $stat)
        @php
            $rank = $i + 1;
            $pct = $maxPoints > 0 ? round(($stat['points'] / $maxPoints) * 100) : 0;
            $isChampion = $rank === 1;
        @endphp
        <div
            class="standings-row group relative rounded-2xl overflow-hidden border transition-all duration-300
                {{ $isChampion ? 'border-yellow-500/30 bg-gradient-to-r from-yellow-950/40 via-slate-900 to-slate-900' : 'border-white/5 bg-slate-900/60 hover:border-white/10' }}"
            style="opacity: 0; transform: translateY(16px);"
            data-delay="{{ $i * 80 }}"
        >
            {{-- Champion ambient glow --}}
            @if($isChampion)
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-px left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-500/50 to-transparent"></div>
            </div>
            @endif

            <div class="relative flex items-center gap-4 px-4 py-4 sm:px-6">

                {{-- Rank --}}
                <div class="flex-shrink-0 w-10 text-center">
                    @if($rank <= 3)
                        <span class="text-2xl leading-none">{{ $medals[$rank - 1] }}</span>
                    @else
                        <span class="text-lg font-bold text-slate-600">{{ $rank }}</span>
                    @endif
                </div>

                {{-- Team dot + name --}}
                <div class="flex-shrink-0 flex items-center gap-3 w-28 sm:w-36">
                    <span class="w-3 h-3 rounded-full flex-shrink-0 {{ $stat['team']->getBgColorClass() }} shadow-lg {{ $stat['team']->getGlowColorClass() }}"></span>
                    <span class="font-bold text-white text-sm sm:text-base capitalize tracking-wide">{{ $stat['team']->name }}</span>
                </div>

                {{-- Bar --}}
                <div class="flex-1 min-w-0">
                    <div class="relative h-2.5 rounded-full bg-white/5 overflow-hidden">
                        <div
                            class="standings-bar absolute left-0 top-0 h-full rounded-full transition-none"
                            style="{{ $stat['team']->getBarGradientStyle() }}; width: 0%;"
                            data-width="{{ $pct }}"
                        ></div>
                    </div>
                </div>

                {{-- Points --}}
                <div class="flex-shrink-0 text-right ml-3 w-20 sm:w-24">
                    <span class="standings-pts text-xl sm:text-2xl font-black tabular-nums
                        {{ $isChampion ? 'text-yellow-400' : 'text-white' }}"
                        data-target="{{ $stat['points'] }}">0</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest
                        {{ $isChampion ? 'text-yellow-600' : 'text-slate-600' }} mt-0.5">pts</span>
                </div>
            </div>
        </div>
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

{{-- Back to Top Button --}}
<button id="back-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-28 right-4 md:bottom-23 md:right-6 z-50 p-3 rounded-full bg-slate-200 text-slate-900 shadow-lg transition-all duration-300 hover:bg-white" style="display: none;">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<script>
const backToTopBtn = document.getElementById('back-to-top');
window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        backToTopBtn.style.display = 'block';
    } else {
        backToTopBtn.style.display = 'none';
    }
});

// Standings animations
(function() {
    const rows = document.querySelectorAll('.standings-row');
    if (!rows.length) return;

    function animateCountUp(el, target, duration) {
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3); // cubic ease-out
            el.textContent = Math.round(ease * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target;
        }
        requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const row = entry.target;
            const delay = parseInt(row.dataset.delay || 0);

            setTimeout(() => {
                // Fade + slide in
                row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';

                // Bar fill
                const bar = row.querySelector('.standings-bar');
                if (bar) {
                    setTimeout(() => {
                        bar.style.transition = 'width 0.9s cubic-bezier(0.22, 1, 0.36, 1)';
                        bar.style.width = bar.dataset.width + '%';
                    }, 150);
                }

                // Count up points
                const ptsEl = row.querySelector('.standings-pts');
                if (ptsEl) {
                    const target = parseInt(ptsEl.dataset.target || 0);
                    setTimeout(() => animateCountUp(ptsEl, target, 900), 200);
                }
            }, delay);

            observer.unobserve(row);
        });
    }, { threshold: 0.15 });

    rows.forEach(row => observer.observe(row));
})();

document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;

    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('opacity-100', i === index);
            slide.classList.toggle('opacity-0', i !== index);
        });
    }

    showSlide(currentSlide);

    setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 3000);
});
</script>
