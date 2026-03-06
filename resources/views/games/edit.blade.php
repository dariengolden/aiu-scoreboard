@extends('layouts.admin')

@section('title', 'Edit Game')

@section('content')

@php
    $sportSlug = $game->category->sport->slug;
    $sportConfig = $game->sport_config;
    $sportType = $sportConfig['type'] ?? 'simple';
    $gameData = $game->game_data ?? [];
    $gameFormat = $game->game_format;
    $maxPeriods = $game->max_periods;
    $periodLabels = $game->period_labels;
    $increments = $sportConfig['increments'] ?? [1];
    $incrementLabels = $sportConfig['increment_labels'] ?? ['+1'];
    $formats = $sportConfig['formats'] ?? null;
@endphp

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-slate-400 mb-6 flex-wrap">
    <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
    <span>/</span>
    <span class="text-white font-medium">Live Scoring</span>
</div>

{{-- Game header --}}
<div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 mb-6">
    <div class="flex items-center gap-3 mb-3">
        <span class="text-3xl">{{ $game->category->sport->icon }}</span>
        <div>
            <h1 class="text-lg font-black text-white">{{ $game->category->sport->name }} &middot; {{ $game->category->name }}</h1>
            <p class="text-sm text-blue-400 font-semibold">{{ $game->match_label }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <x-team-badge :team="$game->teamHome" />
        <span class="text-slate-500 text-sm font-bold">vs</span>
        <x-team-badge :team="$game->teamAway" />
    </div>
</div>

{{-- Live scoring app (all managed by JS) --}}
<div id="live-scoring-app"
     data-game-id="{{ $game->id }}"
     data-sport-slug="{{ $sportSlug }}"
     data-sport-type="{{ $sportType }}"
     data-game-data='@json($gameData)'
     data-game-format="{{ $gameFormat }}"
     data-max-periods="{{ $maxPeriods }}"
     data-period-labels='@json($periodLabels)'
     data-increments='@json($increments)'
     data-increment-labels='@json($incrementLabels)'
     data-formats='@json($formats)'
     data-status="{{ $game->status }}"
     data-score-home="{{ $game->score_home ?? 0 }}"
     data-score-away="{{ $game->score_away ?? 0 }}"
     data-current-period="{{ $game->current_period ?? '' }}"
     data-team-home-name="{{ $game->teamHome->name }}"
     data-team-away-name="{{ $game->teamAway->name }}"
     data-team-home-color="{{ $game->teamHome->color_hex }}"
     data-team-away-color="{{ $game->teamAway->color_hex }}"
     data-update-url="{{ route('games.live-update', $game) }}"
     data-csrf-token="{{ csrf_token() }}"
     data-notes="{{ $game->notes ?? '' }}">

    {{-- Single toast indicator --}}
    <div id="toast-indicator" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 hidden">
        <div id="toast-inner" class="rounded-xl px-3 py-2 text-xs font-medium flex items-center gap-2">
            <span id="toast-icon"></span>
            <span id="toast-text"></span>
        </div>
    </div>

    {{-- Status selector --}}
    <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 space-y-4 mb-5">
        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Game Status</h2>
        <div class="grid grid-cols-3 gap-2" id="status-selector">
            @foreach(['upcoming' => 'Upcoming', 'in_progress' => 'Live', 'completed' => 'Completed'] as $val => $label)
            <button type="button" data-status="{{ $val }}"
                    class="status-btn w-full text-center py-3 rounded-xl text-xs font-bold border-2 transition-colors
                        {{ $game->status === $val ? 'border-blue-500 bg-blue-500/20 text-blue-300' : 'border-white/10 text-slate-400 hover:border-white/20' }}">
                @if($val === 'in_progress')<span class="w-2 h-2 rounded-full bg-green-400 inline-block mr-1 animate-pulse"></span>@endif
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Format selector (for set-based sports with format choice) --}}
    @if($formats)
    <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 space-y-4 mb-5">
        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Match Format</h2>
        <div class="grid grid-cols-{{ count($formats) }} gap-2" id="format-selector">
            @foreach($formats as $fVal => $fLabel)
            <button type="button" data-format="{{ $fVal }}"
                    class="format-btn w-full text-center py-3 rounded-xl text-xs font-bold border-2 transition-colors
                        {{ ($gameFormat ?? $sportConfig['default_format']) === $fVal ? 'border-blue-500 bg-blue-500/20 text-blue-300' : 'border-white/10 text-slate-400 hover:border-white/20' }}">
                {{ $fLabel }}
            </button>
            @endforeach
        </div>
        <p class="text-xs text-slate-500">Changing format will reset all set scores.</p>
    </div>
    @endif

    {{-- Main scoreboard --}}
    <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 mb-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Total Score</h2>
            <span id="period-display" class="text-xs font-bold text-blue-400 uppercase tracking-wider">
                {{ $game->current_period ?? '' }}
            </span>
        </div>

        {{-- Big score display --}}
        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4 mb-4">
            {{-- Home --}}
            <div class="text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                    <span class="text-sm font-bold text-white">{{ $game->teamHome->name }}</span>
                </div>
                <div id="total-score-home" class="text-5xl font-black text-white tabular-nums">
                    {{ $game->score_home ?? 0 }}
                </div>
            </div>

            <div class="text-slate-500 text-lg font-bold">&ndash;</div>

            {{-- Away --}}
            <div class="text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $game->teamAway->color_hex }}"></span>
                    <span class="text-sm font-bold text-white">{{ $game->teamAway->name }}</span>
                </div>
                <div id="total-score-away" class="text-5xl font-black text-white tabular-nums">
                    {{ $game->score_away ?? 0 }}
                </div>
            </div>
        </div>

        {{-- Custom total override (collapsible) --}}
        <details class="group">
            <summary class="text-xs text-slate-500 cursor-pointer hover:text-slate-300 transition-colors select-none">
                Edit totals manually
            </summary>
            <div class="grid grid-cols-2 gap-4 mt-3 pt-3 border-t border-white/5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">{{ $game->teamHome->name }}</label>
                    <input type="number" id="override-score-home" min="0"
                           value="{{ $game->score_home ?? 0 }}"
                           class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg font-black tabular-nums"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">{{ $game->teamAway->name }}</label>
                    <input type="number" id="override-score-away" min="0"
                           value="{{ $game->score_away ?? 0 }}"
                           class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg font-black tabular-nums"
                           placeholder="0">
                </div>
                <div class="col-span-2">
                    <button type="button" id="apply-override-btn"
                            class="w-full bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold py-2 rounded-xl transition-colors">
                        Apply Manual Score
                    </button>
                </div>
            </div>
        </details>
    </div>

    {{-- Period/Set scoring (sport-specific) --}}
    @if($sportType !== 'time' && $sportType !== 'simple')
    <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 mb-5" id="period-scoring-section">
        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4" id="period-section-title">
            @if($sportType === 'sets')
                Set Scores
            @elseif($sportType === 'quarters')
                Quarter Scores
            @elseif($sportType === 'halves')
                Half Scores
            @endif
        </h2>

        {{-- Period tabs --}}
        <div class="flex gap-1.5 mb-4 flex-wrap" id="period-tabs">
            {{-- Generated by JS --}}
        </div>

        {{-- Active period scoring --}}
        <div id="active-period-scoring" class="space-y-4">
            {{-- Home team scoring --}}
            <div class="bg-[#0f172a] rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                        <span class="text-sm font-bold text-white">{{ $game->teamHome->name }}</span>
                    </div>
                    <span id="period-score-home" class="text-2xl font-black text-white tabular-nums">0</span>
                </div>
                <div class="bg-[#1e293b]/50 rounded-xl border border-white/5 p-2">
                    <div class="flex items-center gap-1.5">
                        @if(count($increments) > 1)
                            {{-- Multi-increment sport (e.g. Basketball): full button bar --}}
                            {{-- Decrement buttons (largest to smallest) --}}
                            @foreach(array_reverse($increments, true) as $i => $inc)
                            <button type="button" data-team="home" data-delta="-{{ $inc }}"
                                    class="score-btn shrink-0 w-11 h-10 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-xs flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                -{{ $inc }}
                            </button>
                            @endforeach

                            {{-- Generic minus (uses custom input value) --}}
                            <button type="button" data-team="home"
                                    class="custom-delta-btn shrink-0 w-8 h-10 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                    data-direction="-1">
                                &minus;
                            </button>

                            {{-- Custom input (center) --}}
                            <input type="number" data-team="home" min="1"
                                   class="period-custom-input shrink-0 w-16 h-10 bg-[#0f172a] border border-white/10 rounded-lg text-white text-xs text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                   placeholder="Custom">

                            {{-- Generic plus (uses custom input value) --}}
                            <button type="button" data-team="home"
                                    class="custom-delta-btn shrink-0 w-8 h-10 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                    data-direction="1">
                                +
                            </button>

                            {{-- Increment buttons (smallest to largest, bigger values get more space) --}}
                            @foreach($increments as $i => $inc)
                            <button type="button" data-team="home" data-delta="{{ $inc }}"
                                    class="score-btn h-10 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-xs flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all {{ $inc >= 2 ? 'flex-1 min-w-[3rem]' : 'shrink-0 w-11' }}">
                                {{ $incrementLabels[$i] }}
                            </button>
                            @endforeach
                        @else
                            {{-- Single-increment sport (e.g. Soccer, Volleyball) --}}
                            {{-- Labeled decrement --}}
                            <button type="button" data-team="home" data-delta="-1"
                                    class="score-btn flex-1 h-12 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                -1
                            </button>

                            {{-- Custom minus (uses custom input value) --}}
                            <button type="button" data-team="home"
                                    class="custom-delta-btn shrink-0 w-9 h-12 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                    data-direction="-1">
                                &minus;
                            </button>

                            {{-- Custom input (center) --}}
                            <input type="number" data-team="home" min="1"
                                   class="period-custom-input shrink-0 w-20 h-12 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                   placeholder="Custom">

                            {{-- Custom plus (uses custom input value) --}}
                            <button type="button" data-team="home"
                                    class="custom-delta-btn shrink-0 w-9 h-12 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                    data-direction="1">
                                +
                            </button>

                            {{-- Labeled increment --}}
                            <button type="button" data-team="home" data-delta="1"
                                    class="score-btn flex-1 h-12 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                {{ $incrementLabels[0] }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Away team scoring --}}
            <div class="bg-[#0f172a] rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $game->teamAway->color_hex }}"></span>
                        <span class="text-sm font-bold text-white">{{ $game->teamAway->name }}</span>
                    </div>
                    <span id="period-score-away" class="text-2xl font-black text-white tabular-nums">0</span>
                </div>
                <div class="bg-[#1e293b]/50 rounded-xl border border-white/5 p-2">
                    <div class="flex items-center gap-1.5">
                        @if(count($increments) > 1)
                            {{-- Multi-increment sport (e.g. Basketball): full button bar --}}
                            {{-- Decrement buttons (largest to smallest) --}}
                            @foreach(array_reverse($increments, true) as $i => $inc)
                            <button type="button" data-team="away" data-delta="-{{ $inc }}"
                                    class="score-btn shrink-0 w-11 h-10 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-xs flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                -{{ $inc }}
                            </button>
                            @endforeach

                            {{-- Generic minus (uses custom input value) --}}
                            <button type="button" data-team="away"
                                    class="custom-delta-btn shrink-0 w-8 h-10 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                    data-direction="-1">
                                &minus;
                            </button>

                            {{-- Custom input (center) --}}
                            <input type="number" data-team="away" min="1"
                                   class="period-custom-input shrink-0 w-16 h-10 bg-[#0f172a] border border-white/10 rounded-lg text-white text-xs text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                   placeholder="Custom">

                            {{-- Generic plus (uses custom input value) --}}
                            <button type="button" data-team="away"
                                    class="custom-delta-btn shrink-0 w-8 h-10 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                    data-direction="1">
                                +
                            </button>

                            {{-- Increment buttons (smallest to largest, bigger values get more space) --}}
                            @foreach($increments as $i => $inc)
                            <button type="button" data-team="away" data-delta="{{ $inc }}"
                                    class="score-btn h-10 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-xs flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all {{ $inc >= 2 ? 'flex-1 min-w-[3rem]' : 'shrink-0 w-11' }}">
                                {{ $incrementLabels[$i] }}
                            </button>
                            @endforeach
                        @else
                            {{-- Single-increment sport (e.g. Soccer, Volleyball) --}}
                            {{-- Labeled decrement --}}
                            <button type="button" data-team="away" data-delta="-1"
                                    class="score-btn flex-1 h-12 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                -1
                            </button>

                            {{-- Custom minus (uses custom input value) --}}
                            <button type="button" data-team="away"
                                    class="custom-delta-btn shrink-0 w-9 h-12 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                    data-direction="-1">
                                &minus;
                            </button>

                            {{-- Custom input (center) --}}
                            <input type="number" data-team="away" min="1"
                                   class="period-custom-input shrink-0 w-20 h-12 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                   placeholder="Custom">

                            {{-- Custom plus (uses custom input value) --}}
                            <button type="button" data-team="away"
                                    class="custom-delta-btn shrink-0 w-9 h-12 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                    data-direction="1">
                                +
                            </button>

                            {{-- Labeled increment --}}
                            <button type="button" data-team="away" data-delta="1"
                                    class="score-btn flex-1 h-12 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                {{ $incrementLabels[0] }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Period summary table --}}
        <div class="mt-4 overflow-x-auto" id="period-summary">
            {{-- Generated by JS --}}
        </div>
    </div>
    @endif

    {{-- Schedule, Location & Notes (still form-based, saved via AJAX too) --}}
    <form method="POST" action="{{ route('games.update', $game) }}" id="details-form" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Hidden fields synced by JS --}}
        <input type="hidden" name="status" id="hidden-status" value="{{ $game->status }}">
        <input type="hidden" name="score_home" id="hidden-score-home" value="{{ $game->score_home ?? '' }}">
        <input type="hidden" name="score_away" id="hidden-score-away" value="{{ $game->score_away ?? '' }}">

        {{-- Schedule & Location --}}
        <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 space-y-4">
            <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Schedule & Location</h2>
            <div>
                <label for="scheduled_at" class="block text-xs font-semibold text-slate-400 mb-2">Date & Time</label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                       value="{{ old('scheduled_at', $game->scheduled_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-3 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 [color-scheme:dark]">
            </div>
            <div>
                <label for="location" class="block text-xs font-semibold text-slate-400 mb-2">Location / Court</label>
                <input type="text" id="location" name="location"
                       value="{{ old('location', $game->location) }}"
                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-3 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-500"
                       placeholder="e.g. Main Court, Field A">
            </div>
        </div>

        {{-- Notes --}}
        <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5">
            <label for="notes" class="block text-xs font-semibold text-slate-400 mb-2">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-3 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-500 resize-none"
                      placeholder="Any notes about this game...">{{ old('notes', $game->notes) }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pb-4">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold py-4 rounded-xl transition-colors text-sm">
                Save Details & Return
            </button>
            <a href="{{ route('dashboard') }}"
               class="flex-1 bg-[#1e293b] hover:bg-[#243044] text-slate-300 font-bold py-4 rounded-xl transition-colors text-sm text-center border border-white/10">
                Back to Dashboard
            </a>
        </div>
    </form>

    {{-- Reset Match --}}
    <form id="reset-form" action="{{ route('games.reset', $game) }}" method="POST" class="pb-6">
        @csrf
        <button type="button" onclick="confirmReset()"
                class="w-full bg-red-600/15 hover:bg-red-600/25 active:bg-red-600/35 text-red-400 font-bold py-4 rounded-xl transition-colors text-sm border border-red-500/20">
            Reset Match
        </button>
    </form>
</div>

<script>
function confirmReset() {
    if (confirm('Are you sure you want to reset this match? All scores, game data, and notes will be cleared. This cannot be undone.')) {
        document.getElementById('reset-form').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const app = document.getElementById('live-scoring-app');
    const config = {
        gameId: app.dataset.gameId,
        sportSlug: app.dataset.sportSlug,
        sportType: app.dataset.sportType,
        updateUrl: app.dataset.updateUrl,
        csrfToken: app.dataset.csrfToken,
        teamHomeName: app.dataset.teamHomeName,
        teamAwayName: app.dataset.teamAwayName,
        teamHomeColor: app.dataset.teamHomeColor,
        teamAwayColor: app.dataset.teamAwayColor,
    };

    // ── State ────────────────────────────────────────────────────────────
    let gameData = JSON.parse(app.dataset.gameData || '{}');
    let gameFormat = app.dataset.gameFormat || null;
    let status = app.dataset.status;
    let scoreHome = parseInt(app.dataset.scoreHome) || 0;
    let scoreAway = parseInt(app.dataset.scoreAway) || 0;
    let currentPeriod = app.dataset.currentPeriod || '';
    let maxPeriods = parseInt(app.dataset.maxPeriods) || 1;
    let periodLabels = JSON.parse(app.dataset.periodLabels || '[]');
    let increments = JSON.parse(app.dataset.increments || '[1]');
    let incrementLabels = JSON.parse(app.dataset.incrementLabels || '["+1"]');
    let formats = JSON.parse(app.dataset.formats || 'null');
    let activePeriodIndex = 0;
    let saveTimeout = null;
    let saving = false;
    let indicatorTimeout = null;
    let pendingSave = false;

    // Determine initial active period from game data
    if (config.sportType === 'sets') {
        activePeriodIndex = gameData.current_set ?? 0;
    } else if (config.sportType === 'quarters' || config.sportType === 'halves') {
        activePeriodIndex = gameData.current_period ?? 0;
    }

    // ── DOM refs ─────────────────────────────────────────────────────────
    const totalScoreHome = document.getElementById('total-score-home');
    const totalScoreAway = document.getElementById('total-score-away');
    const periodScoreHome = document.getElementById('period-score-home');
    const periodScoreAway = document.getElementById('period-score-away');
    const periodTabs = document.getElementById('period-tabs');
    const periodSummary = document.getElementById('period-summary');
    const periodDisplay = document.getElementById('period-display');
    const toastIndicator = document.getElementById('toast-indicator');
    const toastInner = document.getElementById('toast-inner');
    const toastIcon = document.getElementById('toast-icon');
    const toastText = document.getElementById('toast-text');
    const hiddenStatus = document.getElementById('hidden-status');
    const hiddenScoreHome = document.getElementById('hidden-score-home');
    const hiddenScoreAway = document.getElementById('hidden-score-away');

    // ── Period tabs ──────────────────────────────────────────────────────
    function renderPeriodTabs() {
        if (!periodTabs) return;
        periodTabs.innerHTML = '';
        for (let i = 0; i < maxPeriods; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = periodLabels[i] || `P${i+1}`;
            btn.className = `period-tab px-3 py-2 rounded-xl text-xs font-bold border-2 transition-colors ${
                i === activePeriodIndex
                    ? 'border-blue-500 bg-blue-500/20 text-blue-300'
                    : 'border-white/10 text-slate-400 hover:border-white/20'
            }`;
            btn.addEventListener('click', () => switchPeriod(i));
            periodTabs.appendChild(btn);
        }
    }

    function switchPeriod(index) {
        activePeriodIndex = index;
        updateCurrentPeriodLabel();
        renderPeriodTabs();
        renderPeriodScores();
        renderPeriodSummary();
    }

    function updateCurrentPeriodLabel() {
        const label = periodLabels[activePeriodIndex] || '';
        currentPeriod = label;
        if (periodDisplay) periodDisplay.textContent = label;

        // Update game_data current tracker
        if (config.sportType === 'sets') {
            gameData.current_set = activePeriodIndex;
        } else {
            gameData.current_period = activePeriodIndex;
        }
    }

    // ── Render scores ────────────────────────────────────────────────────
    function getPeriodData(index) {
        if (config.sportType === 'sets') {
            return (gameData.sets || [])[index] || { home: 0, away: 0 };
        }
        return (gameData.periods || [])[index] || { home: 0, away: 0 };
    }

    function setPeriodData(index, home, away) {
        home = Math.max(0, home);
        away = Math.max(0, away);

        if (config.sportType === 'sets') {
            if (!gameData.sets) gameData.sets = [];
            while (gameData.sets.length <= index) gameData.sets.push({ home: 0, away: 0 });
            gameData.sets[index] = { home, away };
        } else {
            if (!gameData.periods) gameData.periods = [];
            while (gameData.periods.length <= index) gameData.periods.push({ home: 0, away: 0 });
            gameData.periods[index] = { home, away };
        }
    }

    function renderPeriodScores() {
        const data = getPeriodData(activePeriodIndex);
        if (periodScoreHome) periodScoreHome.textContent = data.home;
        if (periodScoreAway) periodScoreAway.textContent = data.away;

        // Update custom inputs
        document.querySelectorAll('.period-custom-input').forEach(input => {
            const team = input.dataset.team;
            input.value = '';
            input.placeholder = team === 'home' ? data.home : data.away;
        });
    }

    function recalculateTotals() {
        if (config.sportType === 'sets') {
            let winsHome = 0, winsAway = 0;
            (gameData.sets || []).forEach(s => {
                if (s.home > s.away) winsHome++;
                else if (s.away > s.home) winsAway++;
            });
            scoreHome = winsHome;
            scoreAway = winsAway;
        } else if (config.sportType === 'quarters' || config.sportType === 'halves') {
            let totalH = 0, totalA = 0;
            (gameData.periods || []).forEach(p => {
                totalH += (p.home || 0);
                totalA += (p.away || 0);
            });
            scoreHome = totalH;
            scoreAway = totalA;
        }

        if (totalScoreHome) totalScoreHome.textContent = scoreHome;
        if (totalScoreAway) totalScoreAway.textContent = scoreAway;

        // Sync hidden form fields
        if (hiddenScoreHome) hiddenScoreHome.value = scoreHome;
        if (hiddenScoreAway) hiddenScoreAway.value = scoreAway;
    }

    function renderPeriodSummary() {
        if (!periodSummary) return;

        const dataKey = config.sportType === 'sets' ? 'sets' : 'periods';
        const items = gameData[dataKey] || [];

        let html = '<table class="w-full text-xs">';
        html += '<thead><tr class="border-b border-white/5 text-slate-500 uppercase tracking-wider">';
        html += '<th class="text-left py-2 pr-2">Team</th>';
        for (let i = 0; i < maxPeriods; i++) {
            html += `<th class="text-center py-2 px-1 ${i === activePeriodIndex ? 'text-blue-400' : ''}">${periodLabels[i] || 'P'+(i+1)}</th>`;
        }
        html += '<th class="text-center py-2 pl-2 font-bold text-white">Total</th>';
        html += '</tr></thead><tbody>';

        // Home row
        html += '<tr class="border-b border-white/5">';
        html += `<td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:${config.teamHomeColor}"></span><span class="font-semibold text-white">${config.teamHomeName}</span></span></td>`;
        for (let i = 0; i < maxPeriods; i++) {
            const val = (items[i] || {}).home || 0;
            html += `<td class="text-center py-2 px-1 font-bold tabular-nums ${i === activePeriodIndex ? 'text-blue-300' : 'text-slate-300'}">${val}</td>`;
        }
        html += `<td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">${scoreHome}</td>`;
        html += '</tr>';

        // Away row
        html += '<tr>';
        html += `<td class="py-2 pr-2"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color:${config.teamAwayColor}"></span><span class="font-semibold text-white">${config.teamAwayName}</span></span></td>`;
        for (let i = 0; i < maxPeriods; i++) {
            const val = (items[i] || {}).away || 0;
            html += `<td class="text-center py-2 px-1 font-bold tabular-nums ${i === activePeriodIndex ? 'text-blue-300' : 'text-slate-300'}">${val}</td>`;
        }
        html += `<td class="text-center py-2 pl-2 font-black text-white tabular-nums text-sm">${scoreAway}</td>`;
        html += '</tr>';

        html += '</tbody></table>';
        periodSummary.innerHTML = html;
    }

    // ── Score button handlers ────────────────────────────────────────────
    document.querySelectorAll('.score-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const team = btn.dataset.team;
            const delta = parseInt(btn.dataset.delta);
            const data = getPeriodData(activePeriodIndex);

            if (team === 'home') {
                setPeriodData(activePeriodIndex, data.home + delta, data.away);
            } else {
                setPeriodData(activePeriodIndex, data.home, data.away + delta);
            }

            recalculateTotals();
            renderPeriodScores();
            renderPeriodSummary();
            scheduleSave();
        });
    });

    // Custom period inputs: no action on Enter/change — value is only used by +/- buttons
    document.querySelectorAll('.period-custom-input').forEach(input => {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') e.preventDefault();
        });
    });

    // Custom delta buttons (+/- next to custom input: increment/decrement by the custom value)
    document.querySelectorAll('.custom-delta-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const team = btn.dataset.team;
            const direction = parseInt(btn.dataset.direction); // 1 or -1
            // Find the sibling custom input for this team
            const container = btn.closest('.flex');
            const input = container.querySelector(`.period-custom-input[data-team="${team}"]`);
            const customVal = parseInt(input?.value);
            if (!customVal || customVal <= 0) return; // do nothing if no custom value entered

            const delta = direction * customVal;
            const data = getPeriodData(activePeriodIndex);

            if (team === 'home') {
                setPeriodData(activePeriodIndex, data.home + delta, data.away);
            } else {
                setPeriodData(activePeriodIndex, data.home, data.away + delta);
            }

            recalculateTotals();
            renderPeriodScores();
            renderPeriodSummary();
            scheduleSave();
        });
    });

    // ── Manual total override ────────────────────────────────────────────
    const overrideBtn = document.getElementById('apply-override-btn');
    if (overrideBtn) {
        overrideBtn.addEventListener('click', () => {
            const h = parseInt(document.getElementById('override-score-home').value) || 0;
            const a = parseInt(document.getElementById('override-score-away').value) || 0;
            scoreHome = h;
            scoreAway = a;
            if (totalScoreHome) totalScoreHome.textContent = scoreHome;
            if (totalScoreAway) totalScoreAway.textContent = scoreAway;
            if (hiddenScoreHome) hiddenScoreHome.value = scoreHome;
            if (hiddenScoreAway) hiddenScoreAway.value = scoreAway;
            saveNow({ score_home: scoreHome, score_away: scoreAway, status });
        });
    }

    // ── Class toggling helpers ────────────────────────────────────────
    const activeClasses = ['border-blue-500', 'bg-blue-500/20', 'text-blue-300'];
    const inactiveClasses = ['border-white/10', 'text-slate-400', 'hover:border-white/20'];

    function setButtonActive(btn) {
        inactiveClasses.forEach(c => btn.classList.remove(c));
        activeClasses.forEach(c => btn.classList.add(c));
    }
    function setButtonInactive(btn) {
        activeClasses.forEach(c => btn.classList.remove(c));
        inactiveClasses.forEach(c => btn.classList.add(c));
    }

    // ── Status buttons ───────────────────────────────────────────────────
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            status = btn.dataset.status;
            if (hiddenStatus) hiddenStatus.value = status;

            document.querySelectorAll('.status-btn').forEach(b => setButtonInactive(b));
            setButtonActive(btn);

            scheduleSave();
        });
    });

    // ── Format buttons (set-based sports) ────────────────────────────────
    document.querySelectorAll('.format-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const newFormat = btn.dataset.format;
            if (newFormat === gameFormat) return;

            gameFormat = newFormat;
            maxPeriods = newFormat === 'best_of_5' ? 5 : 3;

            // Regenerate period labels
            const type = config.sportType === 'sets' ? 'Set' : 'Period';
            periodLabels = [];
            for (let i = 0; i < maxPeriods; i++) {
                periodLabels.push(type + ' ' + (i + 1));
            }

            // Reset game data
            if (config.sportType === 'sets') {
                gameData = { sets: [], current_set: 0 };
                for (let i = 0; i < maxPeriods; i++) {
                    gameData.sets.push({ home: 0, away: 0 });
                }
            } else {
                gameData = { periods: [], current_period: 0 };
                for (let i = 0; i < maxPeriods; i++) {
                    gameData.periods.push({ home: 0, away: 0 });
                }
            }

            activePeriodIndex = 0;
            recalculateTotals();
            renderPeriodTabs();
            renderPeriodScores();
            renderPeriodSummary();
            updateCurrentPeriodLabel();

            // Update visual state
            document.querySelectorAll('.format-btn').forEach(b => setButtonInactive(b));
            setButtonActive(btn);

            scheduleSave();
        });
    });

    // ── Auto-save with debounce ──────────────────────────────────────────
    function scheduleSave() {
        if (saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => sendUpdate(), 400);
    }

    function saveNow(overridePayload) {
        if (saveTimeout) clearTimeout(saveTimeout);
        sendUpdate(overridePayload);
    }

    async function sendUpdate(overridePayload) {
        if (saving) {
            // Mark that we need another save after current one finishes
            pendingSave = true;
            return;
        }

        saving = true;
        pendingSave = false;

        const payload = overridePayload || {
            game_data: gameData,
            game_format: gameFormat,
            current_period: currentPeriod,
            status: status,
        };

        try {
            const response = await axios.patch(config.updateUrl, payload, {
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                }
            });

            if (response.data.success) {
                const g = response.data.game;
                scoreHome = g.score_home ?? 0;
                scoreAway = g.score_away ?? 0;
                if (totalScoreHome) totalScoreHome.textContent = scoreHome;
                if (totalScoreAway) totalScoreAway.textContent = scoreAway;

                // Update override inputs
                const oh = document.getElementById('override-score-home');
                const oa = document.getElementById('override-score-away');
                if (oh) oh.value = scoreHome;
                if (oa) oa.value = scoreAway;

                showSaveIndicator();
            }
        } catch (err) {
            console.error('Save failed:', err.response?.status, err.response?.data || err.message);
            showErrorIndicator();
            // Retry once after 2s
            setTimeout(() => scheduleSave(), 2000);
        } finally {
            saving = false;
            // If another save was requested while we were saving, fire it now
            if (pendingSave) {
                pendingSave = false;
                scheduleSave();
            }
        }
    }

    const successSvg = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
    const errorSvg = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';

    function showToast(type, duration) {
        if (indicatorTimeout) clearTimeout(indicatorTimeout);

        const isSuccess = type === 'success';
        toastIcon.innerHTML = isSuccess ? successSvg : errorSvg;
        toastText.textContent = isSuccess ? 'Saved' : 'Save failed \u2014 retrying...';

        // Reset classes on inner element
        toastInner.className = 'rounded-xl px-3 py-2 text-xs font-medium flex items-center gap-2 border '
            + (isSuccess
                ? 'bg-green-500/20 border-green-500/40 text-green-300'
                : 'bg-red-500/20 border-red-500/40 text-red-300');

        toastIndicator.classList.remove('hidden');

        indicatorTimeout = setTimeout(() => {
            toastIndicator.classList.add('hidden');
        }, duration);
    }

    function showSaveIndicator() {
        showToast('success', 1500);
    }

    function showErrorIndicator() {
        showToast('error', 3000);
    }

    // ── Initialize ───────────────────────────────────────────────────────
    if (config.sportType !== 'time') {
        updateCurrentPeriodLabel();
        renderPeriodTabs();
        renderPeriodScores();
        recalculateTotals();
        renderPeriodSummary();
    }
});
</script>

@endsection
