@props(['content'])

@php
    $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $content))));
@endphp

<div {{ $attributes->merge(['class' => 'space-y-5 text-[15px] leading-7 text-slate-700']) }}>
    @foreach ($paragraphs as $paragraph)
        <p>{{ $paragraph }}</p>
    @endforeach
</div>