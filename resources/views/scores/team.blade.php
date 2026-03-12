@extends('layouts.app')

@section('title', $team->name . ' — Team Stats')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('scores.index') }}" class="hover:text-white transition-colors">Scores</a>
        <span>/</span>
        <span class="text-white font-medium">{{ $team->name }}</span>
    </div>

    {{-- Team header --}}
    <div class="flex items-center gap-4 mb-8">
        <span class="w-8 h-8 rounded-full" style="background-color: {{ $team->color_hex }}"></span>
        <div>
            <h1 class="text-3xl font-black text-white">{{ $team->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">
                @php
                    $faculties = match($team->color_hex) {
                        '#3B82F6' => 'Faculty of Religious Studies (FRS), Mission Faculty of Nursing (MFON)',
                        '#EF4444' => 'Faculty of Education (FOS), Faculty of Science (FOS)',
                        '#EC4899' => 'Faculty of Arts & Humanities (FAH)',
                        '#A855F7' => 'Faculty of Business Administration (FBA), Faculty of Information Technology (FIT)',
                        default => '',
                    };
                @endphp
                {{ $faculties }}
            </p>
        </div>
    </div>

    {{-- Stats table --}}
    <div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
        <div class="px-4 py-3 border-b border-white/5">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">All Categories</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3">Sport</th>
                        <th class="text-left px-4 py-3">Category</th>
                        <th class="text-center px-2 py-3" title="Position">Pos</th>
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
                    @foreach($categoryStats as $item)
                    <tr class="border-b border-white/5 last:border-b-0 hover:bg-white/5">
                        <td class="px-4 py-3">
                            <a href="{{ route('scores.show', $item['sport']) }}" class="flex items-center gap-2 hover:text-blue-400 transition-colors">
                                <x-sport-icon :sport="$item['sport']" size="md" />
                                <span class="font-semibold text-white">{{ $item['sport']->name }}</span>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $item['category']->name }}</td>
                        <td class="text-center px-2 py-3 font-bold {{ $item['teamPosition'] === 1 ? 'text-yellow-400' : ($item['teamPosition'] ? 'text-white' : 'text-slate-500') }}">
                            {{ $item['teamPosition'] ?? '—' }}
                        </td>
                        <td class="text-center px-2 py-3 text-slate-300">{{ $item['stats']['played'] }}</td>
                        <td class="text-center px-2 py-3 text-green-400 font-medium">{{ $item['stats']['won'] }}</td>
                        <td class="text-center px-2 py-3 text-slate-400">{{ $item['stats']['drawn'] }}</td>
                        <td class="text-center px-2 py-3 text-red-400">{{ $item['stats']['lost'] }}</td>
                        <td class="text-center px-2 py-3 text-slate-400 hidden sm:table-cell">{{ $item['stats']['goals_for'] }}</td>
                        <td class="text-center px-2 py-3 text-slate-400 hidden sm:table-cell">{{ $item['stats']['goals_against'] }}</td>
                        <td class="text-center px-2 py-3 font-medium {{ $item['stats']['goal_difference'] > 0 ? 'text-green-400' : ($item['stats']['goal_difference'] < 0 ? 'text-red-400' : 'text-slate-400') }}">
                            {{ $item['stats']['goal_difference'] > 0 ? '+' : '' }}{{ $item['stats']['goal_difference'] }}
                        </td>
                        <td class="text-center px-4 py-3 font-black text-white text-base">{{ $item['stats']['points'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
