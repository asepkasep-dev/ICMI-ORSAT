@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-6']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">{{ $eyebrow }}</p>
        @endif
        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-forest-950 sm:text-4xl">{{ $title }}</h2>
        @if ($description)
            <p class="mt-4 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @if (trim($slot))
        <div class="shrink-0">{{ $slot }}</div>
    @endif
</div>