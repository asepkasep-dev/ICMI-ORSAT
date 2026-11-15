@props(['color' => 'forest'])

@php
    $classes = match ($color) {
        'gold' => 'bg-gold-100 text-gold-700 ring-gold-300/60',
        'gray' => 'bg-slate-100 text-slate-600 ring-slate-300/60',
        'red' => 'bg-red-50 text-red-700 ring-red-300/60',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-300/60',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-300/60',
        default => 'bg-forest-50 text-forest-700 ring-forest-300/60',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>
    {{ $slot }}
</span>