@extends('layouts.app')

@section('title', 'Results')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-white mb-1">Results</h1>
        <p class="text-slate-400 text-sm">Completed games and final scores</p>
    </div>

    {{-- Sport filter --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6">
        <a href="{{ route('results') }}"
           class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-colors {{ !$selectedSport ? 'bg-blue-600 text-white' : 'bg-[#1e293b] text-slate-300 hover:bg-[#243044] hover:text-white' }}">
            All Sports
        </a>
        @foreach($sports as $sport)
        <a href="{{ route('results', ['sport' => $sport->slug]) }}"
           class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-colors {{ $selectedSport == $sport->slug ? 'bg-blue-600 text-white' : 'bg-[#1e293b] text-slate-300 hover:bg-[#243044] hover:text-white' }}">
            {{ $sport->icon }} {{ $sport->name }}
        </a>
        @endforeach
    </div>

    {{-- Results --}}
    @if($games->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">🏆</div>
        <p class="text-slate-400 font-medium">No results yet.</p>
        <p class="text-slate-500 text-sm mt-1">Games will appear here once completed.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($games as $game)
        <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
            {{-- Top info bar --}}
            <div class="px-4 pt-3 pb-2 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-base">{{ $game->category->sport->icon }}</span>
                    <span class="text-sm text-slate-300 font-medium truncate">{{ $game->category->sport->name }} &middot; {{ $game->category->name }}</span>
                    <span class="text-xs text-slate-500 shrink-0">&middot; {{ $game->match_label }}</span>
                </div>
                <x-status-badge :status="$game->status" />
            </div>

            {{-- Score display --}}
            <div class="px-4 pb-4">
                <div class="flex items-stretch gap-3">
                    {{-- Home --}}
                    <div class="flex-1 flex flex-col items-center bg-white/5 rounded-xl py-3 px-2 {{ $game->winner_id === $game->teamHome->id ? 'ring-2 ring-blue-500/40' : '' }}">
                        <span class="w-3 h-3 rounded-full mb-2" style="background-color: {{ $game->teamHome->color_hex }}"></span>
                        <span class="text-sm font-bold text-white mb-1">{{ $game->teamHome->name }}</span>
                        <span class="text-3xl font-black {{ $game->winner_id === $game->teamHome->id ? 'text-white' : 'text-slate-400' }}">
                            {{ $game->score_home ?? '—' }}
                        </span>
                        @if($game->winner_id === $game->teamHome->id)
                        <span class="text-xs text-yellow-400 font-bold mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            WIN
                        </span>
                        @endif
                    </div>

                    {{-- VS divider --}}
                    <div class="flex flex-col items-center justify-center">
                        <span class="text-slate-600 font-bold text-sm">VS</span>
                    </div>

                    {{-- Away --}}
                    <div class="flex-1 flex flex-col items-center bg-white/5 rounded-xl py-3 px-2 {{ $game->winner_id === $game->teamAway->id ? 'ring-2 ring-blue-500/40' : '' }}">
                        <span class="w-3 h-3 rounded-full mb-2" style="background-color: {{ $game->teamAway->color_hex }}"></span>
                        <span class="text-sm font-bold text-white mb-1">{{ $game->teamAway->name }}</span>
                        <span class="text-3xl font-black {{ $game->winner_id === $game->teamAway->id ? 'text-white' : 'text-slate-400' }}">
                            {{ $game->score_away ?? '—' }}
                        </span>
                        @if($game->winner_id === $game->teamAway->id)
                        <span class="text-xs text-yellow-400 font-bold mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            WIN
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Draw indicator --}}
                @if($game->score_home !== null && $game->score_home === $game->score_away)
                <div class="text-center mt-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-700/50 px-3 py-1 rounded-full">Draw</span>
                </div>
                @endif

                <a href="{{ route('standings.show', [$game->category->sport, $game->category]) }}"
                   class="mt-3 block w-full text-center text-xs text-blue-400 hover:text-blue-300 font-medium py-2 rounded-xl hover:bg-white/5 transition-colors">
                    View Standings &rarr;
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
