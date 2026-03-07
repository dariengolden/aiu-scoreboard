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

    $rows = [];
    if ($type === 'sets') {
        $rows = $gameData['sets'] ?? [];
    } elseif (in_array($type, ['quarters', 'halves'])) {
        $rows = $gameData['periods'] ?? [];
    }

    $hasBreakdown = collect($rows)->contains(fn($item) => ($item['home'] ?? 0) > 0 || ($item['away'] ?? 0) > 0);

    $showYellow = $disciplineConfig['yellow'] ?? false;
    $showRed = $disciplineConfig['red'] ?? false;

    // Only treat cards as "discipline section" content; fouls are not shown.
    $hasCardDisciplineData =
        ($showYellow && (($game->yellow_cards_home ?? 0) > 0 || ($game->yellow_cards_away ?? 0) > 0)) ||
        ($showRed && (($game->red_cards_home ?? 0) > 0 || ($game->red_cards_away ?? 0) > 0));
@endphp

<div class="max-w-5xl mx-auto px-4 py-6">

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
                    {{ $homeTeam->name }} vs {{ $awayTeam->name }}
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    {{ $sport?->name }} @if($category) &middot; {{ $category->name }} @endif
                </p>
            </div>
        </div>
        <div class="flex flex-col items-start md:items-end gap-2">
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

        {{-- Scoreboard --}}
        <div class="space-y-4">
            <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
                <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        {{ $game->match_label }}
                        @if($game->current_period)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 text-[11px] font-bold">
                                {{ $game->current_period }}
                            </span>
                        @endif
                    </div>
                    @if($game->isLive())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Live
                        </span>
                    @elseif($game->isCompleted())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 text-[11px] font-bold uppercase tracking-wider">
                            Final
                        </span>
                    @endif
                </div>

                <div class="px-6 py-6">
                    {{-- Teams + score --}}
                    <div class="space-y-3 mb-2">
                        {{-- Team names row --}}
                        <div class="flex items-center justify-between gap-4">
                            {{-- Home --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $homeTeam->color_hex }}"></span>
                                <span class="font-semibold text-base md:text-lg truncate {{ $game->winner_id === $homeTeam->id ? 'text-white' : 'text-slate-300' }}">
                                    {{ $homeTeam->name }}
                                </span>
                                @if($game->winner_id === $homeTeam->id)
                                    <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Away --}}
                            <div class="flex items-center gap-3 min-w-0 justify-end">
                                @if($game->winner_id === $awayTeam->id)
                                    <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                                <span class="font-semibold text-base md:text-lg truncate text-right {{ $game->winner_id === $awayTeam->id ? 'text-white' : 'text-slate-300' }}">
                                    {{ $awayTeam->name }}
                                </span>
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $awayTeam->color_hex }}"></span>
                            </div>
                        </div>

                        {{-- Scores row --}}
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-3xl md:text-4xl font-black tabular-nums {{ $game->winner_id === $homeTeam->id ? 'text-white' : 'text-slate-300' }}">
                                {{ $game->score_home ?? '—' }}
                            </div>

                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                vs
                            </span>

                            <div class="text-3xl md:text-4xl font-black tabular-nums text-right {{ $game->winner_id === $awayTeam->id ? 'text-white' : 'text-slate-300' }}">
                                {{ $game->score_away ?? '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- Draw indicator --}}
                    @if($game->isCompleted() && $game->score_home !== null && $game->score_home === $game->score_away)
                        <div class="mt-2 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Draw
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Period / Set breakdown --}}
            @if($hasBreakdown)
                <div class="bg-[#0f172a] rounded-2xl border border-white/5 overflow-hidden">
                    <div class="px-4 py-3 border-b border-white/5">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                            Game details
                        </h2>
                    </div>
                    <div class="px-4 py-4 space-y-4">
                        {{-- Discipline: cards only --}}
                        @if($disciplineConfig && $hasCardDisciplineData)
                            <div>
                                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Discipline</h3>
                                <div class="space-y-2 text-xs">
                                    {{-- Home --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $homeTeam->color_hex }}"></span>
                                            <span class="font-semibold text-slate-200">{{ $homeTeam->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($showYellow && ($game->yellow_cards_home ?? 0) !== null)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-300">
                                                    <span class="w-2 h-3 bg-yellow-400 rounded-sm shadow-sm shadow-yellow-400/50"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->yellow_cards_home ?? 0 }}</span>
                                                </span>
                                            @endif
                                            @if($showRed && ($game->red_cards_home ?? 0) !== null)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/10 text-red-300">
                                                    <span class="w-2 h-3 bg-red-500 rounded-sm shadow-sm shadow-red-500/50"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->red_cards_home ?? 0 }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Away --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $awayTeam->color_hex }}"></span>
                                            <span class="font-semibold text-slate-200">{{ $awayTeam->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($showYellow && ($game->yellow_cards_away ?? 0) !== null)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-300">
                                                    <span class="w-2 h-3 bg-yellow-400 rounded-sm shadow-sm shadow-yellow-400/50"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->yellow_cards_away ?? 0 }}</span>
                                                </span>
                                            @endif
                                            @if($showRed && ($game->red_cards_away ?? 0) !== null)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/10 text-red-300">
                                                    <span class="w-2 h-3 bg-red-500 rounded-sm shadow-sm shadow-red-500/50"></span>
                                                    <span class="font-bold tabular-nums">{{ $game->red_cards_away ?? 0 }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Score breakdown --}}
                        @if($hasBreakdown)
                            <div class="{{ $disciplineConfig && $hasCardDisciplineData ? 'pt-4 border-t border-white/5' : '' }}">
                                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">
                                    @if($type === 'sets')
                                        Score by set
                                    @elseif($type === 'quarters')
                                        Score by quarter
                                    @elseif($type === 'halves')
                                        Score by half
                                    @else
                                        Score breakdown
                                    @endif
                                </h3>
                                <div class="space-y-2">
                                    @foreach($rows as $i => $row)
                                        @php
                                            $home = $row['home'] ?? 0;
                                            $away = $row['away'] ?? 0;
                                            $label = $labels[$i] ?? 'Period '.($i + 1);
                                            $homeIsAhead = $home > $away;
                                            $awayIsAhead = $away > $home;
                                        @endphp
                                        @if($home > 0 || $away > 0)
                                            <div class="px-3 py-2 rounded-xl bg-slate-900/60 border border-white/5">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[11px] font-semibold uppercase tracking-wider text-slate-300">
                                                            {{ $label }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-4 text-xs md:text-sm tabular-nums">
                                                        <div class="flex items-center gap-1">
                                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $homeTeam->color_hex }}"></span>
                                                            <span class="font-semibold {{ $homeIsAhead ? 'text-white' : 'text-slate-300' }}">
                                                                {{ $home }}
                                                            </span>
                                                        </div>
                                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                                                            vs
                                                        </span>
                                                        <div class="flex items-center gap-1">
                                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $awayTeam->color_hex }}"></span>
                                                            <span class="font-semibold {{ $awayIsAhead ? 'text-white' : 'text-slate-300' }}">
                                                                {{ $away }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

