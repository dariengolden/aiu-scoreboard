@extends('layouts.app')

@section('title', 'Scores')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-white">Choose a sport</h1>
        <p class="text-slate-400 text-sm mt-1">View categories, standings, and scores.</p>
    </div>

    {{-- Sports grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @foreach($sports as $sport)
        <x-sport-card :sport="$sport" />
        @endforeach
    </div>

</div>

@endsection
