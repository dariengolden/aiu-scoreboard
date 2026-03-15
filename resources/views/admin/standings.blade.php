@extends('layouts.admin')

@section('title', 'Team Standings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Team Standings</h1>
        <button onclick="document.body.classList.toggle('show-details')" class="text-sm px-3 py-1.5 rounded-lg font-medium bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
            Show Details
        </button>
    </div>

    {{-- Overall Standings --}}
    <div class="bg-slate-800 rounded-xl border border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10 bg-slate-700/50">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Overall Standings
            </h2>
            <p class="text-sm text-slate-400 mt-1">Combined points across all sports</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-700/30">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Team</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">P</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">W</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">D</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">L</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GF</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GA</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GD</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Pts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @php $rank = 1 @endphp
                    @foreach($overallPoints as $teamId => $stat)
                    @php
                        $gd = $stat['goals_for'] - $stat['goals_against'];
                        $played = 0;
                        $won = 0;
                        $drawn = 0;
                        $lost = 0;
                        foreach ($sports as $sport) {
                            foreach ($sport->categories as $category) {
                                foreach ($categoryStandings[$category->id] ?? [] as $catTeamId => $catStats) {
                                    if ($catTeamId == $teamId) {
                                        $played += $catStats['played'];
                                        $won += $catStats['won'];
                                        $drawn += $catStats['drawn'];
                                        $lost += $catStats['lost'];
                                    }
                                }
                            }
                        }
                    @endphp
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                @if($rank === 1) bg-yellow-500/20 text-yellow-400
                                @elseif($rank === 2) bg-slate-300/20 text-slate-300
                                @elseif($rank === 3) bg-amber-600/20 text-amber-500
                                @else text-slate-400 @endif">
                                {{ $rank }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full {{ $stat['team']->getBgColorClass() }}"></span>
                                <span class="font-medium text-white">{{ ucfirst($stat['team']->name) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $played }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $won }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $drawn }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $lost }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $stat['goals_for'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $stat['goals_against'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center details-col {{ $gd >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $gd > 0 ? '+' : '' }}{{ $gd }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1 rounded-lg text-sm font-bold bg-blue-500/20 text-blue-400">
                                {{ $stat['points'] }}
                            </span>
                        </td>
                    </tr>
                    @php $rank++ @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Standings by Sport --}}
    @foreach($sports as $sport)
    @php $sportData = $sportStandings[$sport->slug] ?? null @endphp
    @if($sportData && count($sportData['standings']) > 0)
    <div class="bg-slate-800 rounded-xl border border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10 bg-slate-700/50">
            <h2 class="text-lg font-semibold text-white">{{ $sport->name }}</h2>
            <p class="text-sm text-slate-400 mt-1">Points from {{ $sport->categories->count() }} {{ Str::plural('category', $sport->categories->count()) }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-700/30">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Team</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">P</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">W</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">D</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">L</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GF</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GA</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider details-col">GD</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Pts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @php $rank = 1 @endphp
                    @foreach($sportData['standings'] as $teamId => $stat)
                    @php
                        $gd = $stat['goals_for'] - $stat['goals_against'];
                        $played = 0;
                        $won = 0;
                        $drawn = 0;
                        $lost = 0;
                        foreach ($sport->categories as $category) {
                            foreach ($categoryStandings[$category->id] ?? [] as $catTeamId => $catStats) {
                                if ($catTeamId == $teamId) {
                                    $played += $catStats['played'];
                                    $won += $catStats['won'];
                                    $drawn += $catStats['drawn'];
                                    $lost += $catStats['lost'];
                                }
                            }
                        }
                    @endphp
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                @if($rank === 1) bg-yellow-500/20 text-yellow-400
                                @elseif($rank === 2) bg-slate-300/20 text-slate-300
                                @elseif($rank === 3) bg-amber-600/20 text-amber-500
                                @else text-slate-400 @endif">
                                {{ $rank }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full {{ $stat['team']->getBgColorClass() }}"></span>
                                <span class="font-medium text-white">{{ ucfirst($stat['team']->name) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $played }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $won }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $drawn }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $lost }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $stat['goals_for'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-slate-300 details-col">{{ $stat['goals_against'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center details-col {{ $gd >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $gd > 0 ? '+' : '' }}{{ $gd }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1 rounded-lg text-sm font-bold bg-blue-500/20 text-blue-400">
                                {{ $stat['points'] }}
                            </span>
                        </td>
                    </tr>
                    @php $rank++ @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</div>

<style>
body:not(.show-details) .details-col {
    display: none;
}
</style>
@endsection
