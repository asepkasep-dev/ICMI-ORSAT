@props(['name', 'src' => null])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter(fn ($w) => $w !== '')
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => 'rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-full bg-forest-900 font-display font-bold text-gold-400']) }} aria-hidden="true">
        {{ $initials ?: 'IC' }}
    </span>
@endif