@props(['team', 'size' => 'md'])

@php
$sizes = [
    'sm' => 'text-xs px-2 py-0.5',
    'md' => 'text-sm px-2.5 py-1',
    'lg' => 'text-base px-3 py-1.5',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($team)
<span class="inline-flex items-center gap-1.5 rounded-full font-semibold {{ $sizeClass }}"
      style="background-color: {{ $team->color_hex }}22; color: {{ $team->color_hex }}; border: 1px solid {{ $team->color_hex }}44;">
    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $team->color_hex }}"></span>
    {{ $team->name }}
</span>
@else
<span class="inline-flex items-center gap-1.5 rounded-full font-semibold {{ $sizeClass }} bg-slate-700/50 text-slate-400 border border-slate-700">
    <span class="w-2 h-2 rounded-full bg-slate-600 shrink-0"></span>
    TBD
</span>
@endif
