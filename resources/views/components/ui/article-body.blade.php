@props(['content'])

<div {{ $attributes->merge(['class' => 'article-prose']) }}>
    {!! \App\Support\ContentSanitizer::clean($content) !!}
</div>