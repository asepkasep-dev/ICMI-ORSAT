@props(['name', 'label', 'value' => null, 'checked' => false])

<label {{ $attributes->except(['wire:model', 'checked'])->merge(['class' => 'flex cursor-pointer items-center gap-2.5 rounded-xl border border-forest-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 transition hover:border-forest-400 has-[:checked]:border-forest-600 has-[:checked]:bg-forest-50 has-[:checked]:text-forest-900']) }}>
    <input type="checkbox" name="{{ $name }}" @if($value !== null) value="{{ $value }}" @endif @checked($checked)
           class="h-4 w-4 rounded border-forest-300 accent-forest-700 focus:ring-2 focus:ring-forest-500/30"
           {{ $attributes->only('wire:model') }}>
    {{ $label }}
</label>