@props(['dark' => false, 'placeholders' => true])
@php
    $links = collect([
        ['label' => 'Instagram', 'value' => config('icmi.social.instagram'), 'icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069Zm0 1.802c-3.153 0-3.507.012-4.746.068-2.695.123-3.995 1.44-4.118 4.118-.056 1.239-.067 1.593-.067 4.746 0 3.153.011 3.507.067 4.746.123 2.678 1.423 3.995 4.118 4.118 1.239.056 1.593.068 4.746.068 3.153 0 3.507-.012 4.746-.068 2.695-.123 3.995-1.44 4.118-4.118.056-1.239.068-1.593.068-4.746 0-3.153-.012-3.507-.068-4.746-.123-2.678-1.423-3.995-4.118-4.118-1.239-.056-1.593-.068-4.746-.068Zm0 3.064a4.971 4.971 0 1 1 0 9.942 4.971 4.971 0 0 1 0-9.942Zm0 8.168a3.197 3.197 0 1 0 0-6.394 3.197 3.197 0 0 0 0 6.394Zm6.406-8.367a1.162 1.162 0 1 1-2.324 0 1.162 1.162 0 0 1 2.324 0Z'],
        ['label' => 'Facebook', 'value' => config('icmi.social.facebook'), 'icon' => 'M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.19 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.9 3.78-3.9 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.75 8.44-4.92 8.44-9.94Z'],
        ['label' => 'YouTube', 'value' => config('icmi.social.youtube'), 'icon' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z'],
        ['label' => 'TikTok', 'value' => config('icmi.social.tiktok'), 'icon' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07Z'],
    ])->when(! $placeholders, fn ($c) => $c->filter(fn ($s) => filled($s['value'])));

    $linkClass = $dark
        ? 'border-white/15 bg-white/5 text-forest-100 hover:border-gold-400/60 hover:bg-gold-400/10 hover:text-gold-300'
        : 'border-forest-200 bg-white text-forest-700 hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300';
    $emptyClass = $dark
        ? 'border-dashed border-white/15 text-forest-300/50'
        : 'border-dashed border-forest-200 bg-forest-50 text-forest-300';
@endphp

@if ($links->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
        @foreach ($links as $social)
            @if (filled($social['value']))
                <a href="{{ $social['value'] }}" target="_blank" rel="noopener noreferrer"
                   class="flex h-11 w-11 items-center justify-center rounded-full border transition {{ $linkClass }}"
                   aria-label="{{ $social['label'] }} resmi" title="{{ $social['label'] }}">
                    <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $social['icon'] }}"/></svg>
                </a>
            @else
                <span class="flex h-11 w-11 items-center justify-center rounded-full border {{ $emptyClass }}"
                      aria-label="{{ $social['label'] }} — belum tersedia" title="{{ $social['label'] }} — data resmi belum tersedia">
                    <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $social['icon'] }}"/></svg>
                </span>
            @endif
        @endforeach
    </div>
@else
    {{ $slot }}
@endif
