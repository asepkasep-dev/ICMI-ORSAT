@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-4']) }}>
    <div>
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-forest-950">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @if (trim($slot))
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>