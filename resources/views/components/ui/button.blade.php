@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = match ($variant) {
        'gold' => 'bg-gold-400 text-forest-950 hover:bg-gold-300 focus-visible:outline-gold-500 shadow-sm shadow-gold-600/20',
        'outline' => 'border border-forest-200 bg-white text-forest-800 hover:border-forest-400 hover:bg-forest-50 focus-visible:outline-forest-600',
        'ghost' => 'text-forest-700 hover:bg-forest-50 focus-visible:outline-forest-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600 shadow-sm',
        'light' => 'border border-white/25 bg-white/10 text-white hover:bg-white/20 focus-visible:outline-white backdrop-blur',
        default => 'bg-forest-900 text-white hover:bg-forest-800 focus-visible:outline-forest-700 shadow-sm shadow-forest-900/20',
    };
    $base = "inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60 active:scale-[0.98] {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>
@endif