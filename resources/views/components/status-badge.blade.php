@props(['status'])

@php
$config = match($status) {
    'in_progress' => ['label' => 'LIVE', 'class' => 'bg-green-500 text-white animate-pulse'],
    'completed'   => ['label' => 'Final', 'class' => 'bg-blue-500/30 text-blue-300'],
    default       => ['label' => 'Upcoming', 'class' => 'bg-slate-700 text-slate-400'],
};
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $config['class'] }}">
    {{ $config['label'] }}
</span>
