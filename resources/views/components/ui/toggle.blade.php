@props(['name', 'label', 'hint' => null, 'checked' => false])

<label {{ $attributes->except(['wire:model', 'checked'])->merge(['class' => 'flex cursor-pointer items-center justify-between gap-4']) }}>
    <span>
        <span class="block text-sm font-medium text-forest-900">{{ $label }}</span>
        @if ($hint)
            <span class="mt-0.5 block text-xs text-slate-400">{{ $hint }}</span>
        @endif
    </span>
    <span class="relative inline-flex shrink-0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked($checked) class="peer sr-only" {{ $attributes->only('wire:model') }}>
        <span class="h-6 w-11 rounded-full bg-slate-300 transition-colors peer-checked:bg-forest-700 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-forest-600 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
    </span>
</label>