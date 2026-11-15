@props([
    'name',
    'label',
    'type' => 'text',
])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-forest-900">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->except('class')->merge(['class' => 'block w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 text-sm text-ink shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25']) }}
    />
    @error($name)
        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-600">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>