@props(['sport', 'size' => 'md'])

@php
    $sportObj = is_object($sport) ? $sport : null;
    $slug = $sportObj?->slug ?? (is_string($sport) ? $sport : null);
    $iconPath = $slug ? "images/{$slug}-icon.svg" : null;
    $hasCustomIcon = $iconPath && file_exists(public_path($iconPath));
    
    $sizeClasses = match($size) {
        'xs' => 'w-2.5 h-2.5',
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4.5 h-4.5',
        'lg' => 'w-6 h-6',
        'xl' => 'w-9 h-9',
        default => 'w-4.5 h-4.5',
    };
    
    $textSizeClass = match($size) {
        'xs' => 'text-[11px]',
        'sm' => 'text-sm',
        'md' => 'text-lg',
        'lg' => 'text-2xl',
        'xl' => 'text-4xl',
        default => 'text-lg',
    };
    
    $sportName = $sportObj?->name ?? $slug ?? '';
    $sportIcon = $sportObj?->icon ?? '';
@endphp

@if($hasCustomIcon && $iconPath)
    <img src="{{ asset($iconPath) }}" {{ $attributes->merge(['class' => $sizeClasses . ' object-contain max-w-full max-h-full inline', 'width' => '', 'height' => '']) }} alt="{{ $sportName }}" />
@elseif($sportIcon)
    <span class="{{ $textSizeClass }}">{{ $sportIcon }}</span>
@endif
