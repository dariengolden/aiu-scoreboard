@extends('layouts.admin')

@section('title', 'Edit Game')

@section('content')

@php
    $sportSlug = $game->category->sport->slug;
    $sportConfig = $game->sport_config;
    $sportType = $sportConfig['type'] ?? 'simple';
    $disciplineConfig = $sportConfig['discipline'] ?? null;
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

        {{-- Period/Set scoring (sport-specific) --}}
        @if($sportType !== 'time' && $sportType !== 'simple')
        
        <div class="h-[1px] bg-white/5 my-5 w-full"></div>
        
        <div id="period-scoring-section">
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
        <div id="active-period-scoring" class="grid grid-cols-2 gap-4">
            {{-- Home team scoring --}}
            <div class="bg-[#0f172a] rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                        <span class="text-sm font-bold text-white">{{ $game->teamHome->name }}</span>
                    </div>
                    
                </div>
                <div class="bg-[#1e293b]/50 rounded-xl border border-white/5 p-2">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-1.5 w-full">
                        @if(count($increments) > 1)
                            {{-- Increment buttons (top on mobile, right on desktop) - larger values further from center --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-1 lg:order-3 w-full">
                                @foreach($increments as $i => $inc)
                                <button type="button" data-team="home" data-delta="{{ $inc }}"
                                        class="score-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm lg:text-xs flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                    +{{ $inc }}
                                </button>
                                @endforeach
                            </div>

                            {{-- Custom input section (middle) --}}
                            <div class="flex flex-1 flex-col gap-2 lg:flex-row-reverse lg:gap-1.5 order-2 w-full">
                                {{-- Custom plus (above input on mobile, right of input on desktop) --}}
                                <button type="button" data-team="home"
                                        class="custom-delta-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                        data-direction="1">
                                    +
                                </button>

                                <input type="number" data-team="home" min="1"
                                       class="period-custom-input w-full lg:flex-[2] h-12 lg:h-10 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                       placeholder="Custom">

                                {{-- Custom minus (below input on mobile, left of input on desktop) --}}
                                <button type="button" data-team="home"
                                        class="custom-delta-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                        data-direction="-1">
                                    &minus;
                                </button>
                            </div>

                            {{-- Decrement buttons (bottom on mobile, left on desktop) - larger values further from center --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-3 lg:order-1 w-full">
                                @foreach(array_reverse($increments, true) as $i => $inc)
                                <button type="button" data-team="home" data-delta="-{{ $inc }}"
                                        class="score-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm lg:text-xs flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                    -{{ $inc }}
                                </button>
                                @endforeach
                            </div>
                        @else
                            {{-- Single-increment sport (e.g. Soccer, Volleyball) --}}
                            {{-- Increment button (top on mobile, right on desktop) --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-1 lg:order-3 w-full">
                                <button type="button" data-team="home" data-delta="1"
                                        class="score-btn w-full h-12 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                    +1
                                </button>
                            </div>

                            {{-- Custom input section (middle) --}}
                            <div class="flex flex-1 flex-col gap-2 lg:flex-row-reverse lg:gap-1.5 order-2 w-full">
                                <button type="button" data-team="home"
                                        class="custom-delta-btn w-full h-12 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                        data-direction="1">
                                    +
                                </button>

                                <input type="number" data-team="home" min="1"
                                       class="period-custom-input w-full h-12 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                       placeholder="Custom">

                                <button type="button" data-team="home"
                                        class="custom-delta-btn w-full h-12 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                        data-direction="-1">
                                    &minus;
                                </button>
                            </div>

                            {{-- Decrement button (bottom on mobile, left on desktop) --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-3 lg:order-1 w-full">
                                <button type="button" data-team="home" data-delta="-1"
                                        class="score-btn w-full h-12 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                    -1
                                </button>
                            </div>
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
                    
                </div>
                <div class="bg-[#1e293b]/50 rounded-xl border border-white/5 p-2">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-1.5 w-full">
                        @if(count($increments) > 1)
                            {{-- Increment buttons (top on mobile, right on desktop) - larger values further from center --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-1 lg:order-3 w-full">
                                @foreach($increments as $i => $inc)
                                <button type="button" data-team="away" data-delta="{{ $inc }}"
                                        class="score-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm lg:text-xs flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                    +{{ $inc }}
                                </button>
                                @endforeach
                            </div>

                            {{-- Custom input section (middle) --}}
                            <div class="flex flex-1 flex-col gap-2 lg:flex-row-reverse lg:gap-1.5 order-2 w-full">
                                {{-- Custom plus (above input on mobile, right of input on desktop) --}}
                                <button type="button" data-team="away"
                                        class="custom-delta-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                        data-direction="1">
                                    +
                                </button>

                                <input type="number" data-team="away" min="1"
                                       class="period-custom-input w-full lg:flex-[2] h-12 lg:h-10 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                       placeholder="Custom">

                                {{-- Custom minus (below input on mobile, left of input on desktop) --}}
                                <button type="button" data-team="away"
                                        class="custom-delta-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                        data-direction="-1">
                                    &minus;
                                </button>
                            </div>

                            {{-- Decrement buttons (bottom on mobile, left on desktop) - larger values further from center --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-3 lg:order-1 w-full">
                                @foreach(array_reverse($increments, true) as $i => $inc)
                                <button type="button" data-team="away" data-delta="-{{ $inc }}"
                                        class="score-btn w-full lg:flex-1 h-12 lg:h-10 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm lg:text-xs flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                    -{{ $inc }}
                                </button>
                                @endforeach
                            </div>
                        @else
                            {{-- Single-increment sport (e.g. Soccer, Volleyball) --}}
                            {{-- Increment button (top on mobile, right on desktop) --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-1 lg:order-3 w-full">
                                <button type="button" data-team="away" data-delta="1"
                                        class="score-btn w-full h-12 rounded-lg bg-green-500/20 border border-green-500/30 text-green-400 font-bold text-sm flex items-center justify-center hover:bg-green-500/30 active:scale-95 transition-all">
                                    +1
                                </button>
                            </div>

                            {{-- Custom input section (middle) --}}
                            <div class="flex flex-1 flex-col gap-2 lg:flex-row-reverse lg:gap-1.5 order-2 w-full">
                                <button type="button" data-team="away"
                                        class="custom-delta-btn w-full h-12 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 font-black text-base flex items-center justify-center hover:bg-green-500/20 active:scale-95 transition-all"
                                        data-direction="1">
                                    +
                                </button>

                                <input type="number" data-team="away" min="1"
                                       class="period-custom-input w-full h-12 bg-[#0f172a] border border-white/10 rounded-lg text-white text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 tabular-nums"
                                       placeholder="Custom">

                                <button type="button" data-team="away"
                                        class="custom-delta-btn w-full h-12 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 font-black text-base flex items-center justify-center hover:bg-red-500/20 active:scale-95 transition-all"
                                        data-direction="-1">
                                    &minus;
                                </button>
                            </div>

                            {{-- Decrement button (bottom on mobile, left on desktop) --}}
                            <div class="flex flex-1 flex-col-reverse gap-2 lg:flex-row lg:gap-1.5 order-3 lg:order-1 w-full">
                                <button type="button" data-team="away" data-delta="-1"
                                        class="score-btn w-full h-12 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold text-sm flex items-center justify-center hover:bg-red-500/30 active:scale-95 transition-all">
                                    -1
                                </button>
                            </div>
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
    </div>

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

        {{-- Cards (sport-specific, e.g. soccer) --}}
        @if($disciplineConfig)
        @php
            $showYellow = $disciplineConfig['yellow'] ?? false;
            $showRed = $disciplineConfig['red'] ?? false;
        @endphp
        @if($showYellow || $showRed)
        <div class="bg-[#1e293b] rounded-2xl p-5 border border-white/5 space-y-4">
            <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Discipline</h2>

            <div class="space-y-3">
                <p class="block text-xs font-semibold text-slate-400">Cards</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Home cards --}}
                    <div class="space-y-2">
                        <p class="text-[11px] font-semibold text-slate-500 mb-1">Home &middot; {{ $game->teamHome->name }}</p>
                        <div class="flex items-center gap-2">
                            @if($showYellow)
                            <div class="flex-1">
                                <label class="block text-[10px] font-semibold text-yellow-300 mb-1 uppercase tracking-wider">Yellow</label>
                                <input type="number" name="yellow_cards_home" min="0"
                                       value="{{ old('yellow_cards_home', $game->yellow_cards_home) }}"
                                       class="w-full bg-[#0f172a] border border-yellow-500/40 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 [color-scheme:dark]">
                            </div>
                            @endif
                            @if($showRed)
                            <div class="flex-1">
                                <label class="block text-[10px] font-semibold text-red-300 mb-1 uppercase tracking-wider">Red</label>
                                <input type="number" name="red_cards_home" min="0"
                                       value="{{ old('red_cards_home', $game->red_cards_home) }}"
                                       class="w-full bg-[#0f172a] border border-red-500/40 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-400 [color-scheme:dark]">
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Away cards --}}
                    <div class="space-y-2">
                        <p class="text-[11px] font-semibold text-slate-500 mb-1">Away &middot; {{ $game->teamAway->name }}</p>
                        <div class="flex items-center gap-2">
                            @if($showYellow)
                            <div class="flex-1">
                                <label class="block text-[10px] font-semibold text-yellow-300 mb-1 uppercase tracking-wider">Yellow</label>
                                <input type="number" name="yellow_cards_away" min="0"
                                       value="{{ old('yellow_cards_away', $game->yellow_cards_away) }}"
                                       class="w-full bg-[#0f172a] border border-yellow-500/40 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 [color-scheme:dark]">
                            </div>
                            @endif
                            @if($showRed)
                            <div class="flex-1">
                                <label class="block text-[10px] font-semibold text-red-300 mb-1 uppercase tracking-wider">Red</label>
                                <input type="number" name="red_cards_away" min="0"
                                       value="{{ old('red_cards_away', $game->red_cards_away) }}"
                                       class="w-full bg-[#0f172a] border border-red-500/40 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-400 [color-scheme:dark]">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

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
        <button type="button" id="reset-btn"
                class="w-full bg-red-600/15 hover:bg-red-600/25 active:bg-red-600/35 text-red-400 font-bold py-4 rounded-xl transition-colors text-sm border border-red-500/20">
            Reset Match
        </button>
    </form>
</div>

{{-- Confirmation Modal --}}
<div id="confirm-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-[#1e293b] border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all" id="modal-panel">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2" id="modal-title">Confirm Action</h3>
                <p class="text-sm text-slate-400 mb-6" id="modal-message">Are you sure?</p>
                <div class="flex gap-3">
                    <button type="button" id="modal-cancel" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                        Cancel
                    </button>
                    <button type="button" id="modal-confirm" class="flex-1 bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-xl transition-colors text-sm">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Floating delta animation styles
    const floatStyle = document.createElement('style');
    floatStyle.textContent = `
        @keyframes float-up {
            0% { opacity: 1; transform: translateX(-50%) translateY(0); }
            100% { opacity: 0; transform: translateX(-50%) translateY(-40px); }
        }
        .animate-float-up {
            animation: float-up 0.8s ease-out forwards;
        }
    `;
    document.head.appendChild(floatStyle);

    // ── Confirmation Modal ─────────────────────────────────────────────────
    const modal = document.getElementById('confirm-modal');
    const modalBackdrop = document.getElementById('modal-backdrop');
    const modalPanel = document.getElementById('modal-panel');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const modalCancel = document.getElementById('modal-cancel');
    const modalConfirm = document.getElementById('modal-confirm');

    let modalResolve = null;

    function showModal(title, message, confirmText = 'Confirm', isDanger = true) {
        return new Promise((resolve) => {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalConfirm.textContent = confirmText;
            
            if (isDanger) {
                modalConfirm.className = 'flex-1 bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-xl transition-colors text-sm';
            } else {
                modalConfirm.className = 'flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-colors text-sm';
            }

            modalResolve = resolve;
            modal.classList.remove('hidden');
            modalPanel.classList.add('scale-100');
            modalPanel.classList.remove('scale-95');
        });
    }

    function hideModal() {
        modal.classList.add('hidden');
        if (modalResolve) {
            modalResolve(false);
            modalResolve = null;
        }
    }

    modalBackdrop.addEventListener('click', hideModal);
    modalCancel.addEventListener('click', hideModal);
    modalConfirm.addEventListener('click', () => {
        if (modalResolve) {
            modalResolve(true);
            modalResolve = null;
        }
        hideModal();
    });

    // ── Reset Confirmation ─────────────────────────────────────────────────
    const resetBtn = document.getElementById('reset-btn');
    const resetForm = document.getElementById('reset-form');

    resetBtn.addEventListener('click', async () => {
        const confirmed = await showModal(
            'Reset Match?',
            'Are you sure you want to reset this match? All scores, game data, and notes will be cleared. This cannot be undone.',
            'Reset Match'
        );
        if (confirmed) {
            resetForm.submit();
        }
    });

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

        // Update custom inputs
        document.querySelectorAll('.period-custom-input').forEach(input => {
            input.value = '';
            input.placeholder = '0';
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
            
            // Find the custom input - it's the sibling input element
            // For + button (direction=1), input is nextElementSibling
            // For - button (direction=-1), input is previousElementSibling
            const input = direction === 1 ? btn.nextElementSibling : btn.previousElementSibling;
            const customVal = parseInt(input?.value, 10);
            
            console.log('Custom delta clicked:', team, direction, 'input:', input, 'value:', input?.value, 'parsed:', customVal);
            
            if (!customVal || customVal <= 0 || isNaN(customVal)) {
                console.log('No valid custom value, returning');
                return; // do nothing if no valid custom value entered
            }

            const delta = direction * customVal;
            const data = getPeriodData(activePeriodIndex);

            if (team === 'home') {
                setPeriodData(activePeriodIndex, data.home + delta, data.away);
            } else {
                setPeriodData(activePeriodIndex, data.home, data.away + delta);
            }

            // Show floating animation
            showDeltaAnimation(btn, delta);

            // Clear the custom input
            input.value = '';

            recalculateTotals();
            renderPeriodScores();
            renderPeriodSummary();
            scheduleSave();
        });
    });

    // Floating delta animation
    function showDeltaAnimation(btn, delta) {
        const anim = document.createElement('div');
        const isPositive = delta > 0;
        anim.textContent = (isPositive ? '+' : '') + delta;
        anim.className = 'fixed pointer-events-none text-lg font-black z-50 animate-float-up';
        anim.style.color = isPositive ? '#4ade80' : '#f87171';
        anim.style.left = btn.getBoundingClientRect().left + btn.offsetWidth / 2 + 'px';
        anim.style.top = btn.getBoundingClientRect().top + 'px';
        anim.style.transform = 'translateX(-50%)';
        document.body.appendChild(anim);

        setTimeout(() => anim.remove(), 800);
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
        btn.addEventListener('click', async () => {
            const newFormat = btn.dataset.format;
            if (newFormat === gameFormat) return;

            const confirmed = await showModal(
                'Change Match Format?',
                'Changing the format will reset all set scores. This cannot be undone.',
                'Change Format'
            );
            
            if (!confirmed) return;

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
