@extends('layouts.app')

@section('title', $sport->name . ' — ' . $category->name . ' Standings')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6 flex-wrap">
        <a href="{{ route('scores.index') }}" class="hover:text-white transition-colors">Scores</a>
        <span>/</span>
        <a href="{{ route('scores.show', $sport) }}" class="hover:text-white transition-colors">{{ $sport->name }}</a>
        <span>/</span>
        <span class="text-white font-medium">{{ $category->name }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <span class="text-4xl">{{ $sport->icon }}</span>
            <div>
                <h1 class="text-2xl font-black text-white">{{ $sport->name }}</h1>
                <p class="text-blue-400 font-semibold">{{ $category->name }} — Round Robin</p>
            </div>
        </div>
    </div>

    {{-- Category tab nav --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-8">
        @foreach($sport->categories as $cat)
        <a href="{{ route('standings.show', [$sport, $cat]) }}"
           class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-colors {{ $cat->id === $category->id ? 'bg-blue-600 text-white' : 'bg-[#1e293b] text-slate-300 hover:bg-[#243044] hover:text-white' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>

    {{-- Standings Table --}}
    <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden mb-8">
        <div class="px-4 py-3 border-b border-white/5">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Standings</h2>
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

    {{-- All Games --}}
    <div class="mb-4">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">All Matches</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
    </div>

</div>

@endsection
