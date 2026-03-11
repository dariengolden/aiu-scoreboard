@props(['team'])

@php
$abbreviations = [
    'Blue' => 'FRS, MFON',
    'Red' => 'FOE, FOS',
    'Pink' => 'FAH',
    'Purple' => 'FBA, FIT',
];
$subtitle = $abbreviations[$team->name] ?? 'View stats';
@endphp

<a href="{{ route('scores.team', $team) }}"
   class="group block rounded-2xl p-5 border border-white/10 hover:border-white/30 transition-all active:scale-95"
   style="background-color: {{ $team->color_hex }}20; border-color: {{ $team->color_hex }}40;">
    <div class="flex items-center gap-3">
        <span class="w-4 h-4 rounded-full shrink-0" style="background-color: {{ $team->color_hex }}"></span>
        <h3 class="font-bold text-white text-base group-hover:opacity-80 transition-opacity">{{ $team->name }}</h3>
    </div>
    <p class="text-xs text-slate-400 mt-2">{{ $subtitle }}</p>
</a>
