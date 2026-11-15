@props([
    'label',
    'value',
    'accent' => 'forest',
])

@php
    $iconBg = match ($accent) {
        'gold' => 'bg-gold-100 text-gold-600',
        'red' => 'bg-red-50 text-red-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'gray' => 'bg-slate-100 text-slate-500',
        default => 'bg-forest-50 text-forest-700',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-forest-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $iconBg }}">
            {{ $slot }}
        </span>
    </div>
    <p class="mt-3 font-display text-3xl font-bold tracking-tight text-forest-950">{{ $value }}</p>
</div>