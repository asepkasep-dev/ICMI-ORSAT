@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-forest-200 bg-forest-50/50 px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-forest-100 text-forest-600">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
    </span>
    <h3 class="mt-4 font-display text-base font-semibold text-forest-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-md text-sm leading-relaxed text-slate-500">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>