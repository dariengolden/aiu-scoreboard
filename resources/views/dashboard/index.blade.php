@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@php
function getTeam($id) {
    return $id ? App\Models\Team::find($id) : null;
}
@endphp

{{-- Stats bar --}}
<div class="grid grid-cols-3 gap-3 mb-8">
    <div class="bg-[#1e293b] rounded-2xl p-4 border border-white/5 text-center">
        <p class="text-2xl font-black text-white">{{ $totalGames }}</p>
        <p class="text-xs text-slate-400 mt-0.5 font-medium">Total</p>
    </div>
    <div class="bg-green-500/10 rounded-2xl p-4 border border-green-500/20 text-center">
        <p class="text-2xl font-black text-green-400">{{ $liveGames->count() }}</p>
        <p class="text-xs text-green-400/70 mt-0.5 font-medium">Live</p>
    </div>
    <div class="bg-blue-500/10 rounded-2xl p-4 border border-blue-500/20 text-center">
        <p class="text-2xl font-black text-blue-400">{{ $completedGames }}</p>
        <p class="text-xs text-blue-400/70 mt-0.5 font-medium">Done</p>
    </div>
</div>

{{-- Live games quick-edit --}}
@if($liveGames->isNotEmpty())
<div class="mb-8">
    <h2 class="text-sm font-bold text-green-400 uppercase tracking-widest flex items-center gap-2 mb-4">
        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
        Live Now
    </h2>
    <div class="space-y-3">
        @foreach($liveGames as $game)
        <div class="bg-[#1e293b] rounded-2xl p-4 border border-green-500/20 flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs text-slate-400">{{ $game->category->sport->name }} &middot; {{ $game->category->name }} &middot; {{ $game->match_label }}</p>
                    @if($game->current_period)
                    <span class="text-xs font-bold text-blue-400">{{ $game->current_period }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @php $sportType = $game->sport_config['type'] ?? null; @endphp
                    @if($sportType === 'places')
                        @php $places = $game->game_data['places'] ?? []; @endphp
                        <span class="text-xs text-slate-400">🥇 {{ getTeam($places[1] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">🥈 {{ getTeam($places[2] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">🥉 {{ getTeam($places[3] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">4th {{ getTeam($places[4] ?? null)?->name ?? '—' }}</span>
                    @else
                        <x-team-badge :team="$game->teamHome" size="sm" />
                        @if($game->score_home !== null && $game->score_away !== null)
                        <span class="text-sm font-black text-white tabular-nums">{{ $game->score_home }}&ndash;{{ $game->score_away }}</span>
                        @else
                        <span class="text-slate-500 text-xs">vs</span>
                        @endif
                        <x-team-badge :team="$game->teamAway" size="sm" />
                    @endif
                </div>
            </div>
            <a href="{{ route('games.edit', $game) }}"
               class="shrink-0 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors active:scale-95">
                Update
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Sport filters --}}
@if($sports->isNotEmpty())
<div class="mb-8">
    <div class="bg-[#020617]/60 border border-white/10 rounded-2xl px-4 py-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Filter by sport</p>
        <div class="flex flex-wrap gap-2">
            <button type="button"
                    data-sport-filter="all"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors bg-blue-600 text-white shadow-sm shadow-blue-500/30">
                All sports
            </button>
            @foreach($sports as $sport)
            <button type="button"
                    data-sport-filter="{{ $sport->slug }}"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors bg-[#0f172a] text-slate-300 hover:bg-[#162033] hover:text-white border border-white/5">
                {{ $sport->icon }} {{ $sport->name }}
            </button>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- All games by sport --}}
@foreach($sports as $sport)
<div class="mb-8" data-sport-section="{{ $sport->slug }}">
    <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
        <span>{{ $sport->icon }}</span> {{ $sport->name }}
    </h2>

    @foreach($sport->categories as $category)
    <div class="mb-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">{{ $category->name }}</p>
        <div class="space-y-2">
            @foreach($category->games->sortBy('match_number') as $game)
            <div class="bg-[#1e293b] rounded-xl border border-white/5 flex items-center gap-3 p-3">
                {{-- Match number pill --}}
                <span class="shrink-0 text-xs font-bold px-2 py-1 rounded-lg bg-slate-700/70 text-slate-400">
                    M{{ $game->match_number }}
                </span>

                {{-- Teams --}}
                <div class="flex-1 flex items-center gap-2 min-w-0">
                    @php $sportType = $game->sport_config['type'] ?? null; @endphp
                    @if($sportType === 'places')
                        @php $places = $game->game_data['places'] ?? []; @endphp
                        <span class="text-xs text-slate-400">🥇 {{ getTeam($places[1] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">🥈 {{ getTeam($places[2] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">🥉 {{ getTeam($places[3] ?? null)?->name ?? '—' }}</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs text-slate-400">4th {{ getTeam($places[4] ?? null)?->name ?? '—' }}</span>
                    @else
                        <x-team-badge :team="$game->teamHome" size="sm" />
                        @if($game->score_home !== null && $game->score_away !== null)
                        <span class="text-sm font-black text-white tabular-nums">{{ $game->score_home }}&ndash;{{ $game->score_away }}</span>
                        @else
                        <span class="text-slate-600 text-xs font-bold">vs</span>
                        @endif
                        <x-team-badge :team="$game->teamAway" size="sm" />
                    @endif
                </div>

                {{-- Status + edit --}}
                <div class="shrink-0 flex items-center gap-2">
                    <x-status-badge :status="$game->status" />
                    <a href="{{ route('games.edit', $game) }}"
                       class="text-slate-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endforeach

{{-- Simple client-side sport filter --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('[data-sport-filter]');
    const sections = document.querySelectorAll('[data-sport-section]');

    if (!buttons.length || !sections.length) return;

    function applySportFilter(slug) {
        sections.forEach(section => {
            const sectionSlug = section.getAttribute('data-sport-section');
            const shouldShow = slug === 'all' || sectionSlug === slug;
            section.classList.toggle('hidden', !shouldShow);
        });

        buttons.forEach(button => {
            const isActive = button.getAttribute('data-sport-filter') === slug;
            button.classList.toggle('bg-blue-600', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('bg-[#0f172a]', !isActive && button.getAttribute('data-sport-filter') !== 'all');
            button.classList.toggle('text-slate-300', !isActive && button.getAttribute('data-sport-filter') !== 'all');
        });
    }

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const slug = this.getAttribute('data-sport-filter');
            applySportFilter(slug);
        });
    });

    // Default to showing all sports
    applySportFilter('all');
});
</script>

@endsection
