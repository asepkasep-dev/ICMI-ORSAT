@props([
    'name',
    'label',
    'multiple' => false,
    'hint' => 'JPG, PNG, atau WebP — maksimal 2 MB per file.',
])

<div x-data="{ names: [] }" {{ $attributes->only('class') }}>
    <span class="mb-1.5 block text-sm font-medium text-forest-900">{{ $label }}</span>
    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-forest-200 bg-forest-50/50 px-6 py-8 text-center transition hover:border-forest-400 hover:bg-forest-50">
        <svg class="h-7 w-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
        <span class="text-sm font-semibold text-forest-800">Klik untuk memilih gambar</span>
        <span class="text-xs text-slate-400">{{ $hint }}</span>
        <input type="file" name="{{ $name }}{{ $multiple ? '[]' : '' }}" id="{{ $name }}"
               accept="image/jpeg,image/png,image/webp" @if($multiple) multiple @endif class="sr-only"
               x-on:change="names = Array.from($event.target.files).map(f => f.name)"
               {{ $attributes->only('wire:model') }}>
    </label>
    <ul x-show="names.length" x-cloak class="mt-2 space-y-1">
        <template x-for="n in names" :key="n">
            <li class="flex items-center gap-2 text-xs font-medium text-forest-700">
                <svg class="h-3.5 w-3.5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                <span x-text="n"></span>
            </li>
        </template>
    </ul>
    @error($name)
        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-600">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ $message }}
        </p>
    @enderror
    @error($name.'.*')
        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-600">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>