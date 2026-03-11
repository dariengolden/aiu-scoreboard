@extends('layouts.app')

@section('title', 'Scores')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-white">Sports</h1>
        <p class="text-slate-400 text-sm mt-1">View categories, standings, and scores.</p>
    </div>

    {{-- Sports grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-12">
        @foreach($sports as $sport)
        <x-sport-card :sport="$sport" />
        @endforeach
    </div>

    {{-- My Team Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-black text-white mb-1">Teams</h2>
        <p class="text-slate-400 text-sm mb-6">View team statistics.</p>

        {{-- Team cards grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($teams as $item)
            <x-team-card :team="$item['team']" />
            @endforeach
        </div>
    </div>

</div>

@endsection
