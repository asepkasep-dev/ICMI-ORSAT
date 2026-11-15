@props(['accent' => 'text-gold-400'])

@php($short = (string) config('icmi.short_name'))
{{ \Illuminate\Support\Str::before($short, ' ') }}@if (str_contains($short, ' ')) <span class="{{ $accent }}">{{ \Illuminate\Support\Str::after($short, ' ') }}</span>@endif
