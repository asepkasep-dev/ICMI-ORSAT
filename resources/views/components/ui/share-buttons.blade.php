@props(['url', 'title'])

@php
    $encodedUrl = urlencode($url);
    $encodedText = urlencode($title.' — '.$url);
@endphp

<div x-data="{ copied: false, copy() {
        const done = () => { this.copied = true; setTimeout(() => this.copied = false, 2000); };
        if (navigator.clipboard?.writeText) { navigator.clipboard.writeText('{{ $url }}').then(done); }
        else {
            const ta = document.createElement('textarea');
            ta.value = '{{ $url }}'; document.body.appendChild(ta); ta.select();
            document.execCommand('copy'); document.body.removeChild(ta); done();
        }
    } }"
    {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    <span class="mr-1 text-xs font-bold uppercase tracking-wider text-slate-400">Bagikan</span>
    <a href="https://wa.me/?text={{ $encodedText }}" target="_blank" rel="noopener noreferrer"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-forest-200 bg-white text-forest-700 transition hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300" aria-label="Bagikan ke WhatsApp" title="WhatsApp">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2Zm0 18.03c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.26 8.26 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 4.54 0 8.24 3.7 8.24 8.24 0 4.55-3.7 8.24-8.24 8.24Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.17.25-.64.8-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/></svg>
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-forest-200 bg-white text-forest-700 transition hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300" aria-label="Bagikan ke Facebook" title="Facebook">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.19 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.9 3.78-3.9 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.75 8.44-4.92 8.44-9.94Z"/></svg>
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $encodedText }}" target="_blank" rel="noopener noreferrer"
       class="flex h-9 w-9 items-center justify-center rounded-full border border-forest-200 bg-white text-forest-700 transition hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300" aria-label="Bagikan ke X" title="X">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93Zm-1.29 19.5h2.04L6.49 3.24H4.3l13.31 17.41Z"/></svg>
    </a>
    <button type="button" x-on:click="copy()"
            class="flex h-9 items-center gap-2 rounded-full border border-forest-200 bg-white px-4 text-xs font-semibold text-forest-700 transition hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300"
            :class="copied && 'border-forest-600 bg-forest-900 text-gold-300'">
        <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
        <svg x-show="copied" x-cloak class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        <span x-text="copied ? 'Tersalin!' : 'Salin tautan'"></span>
    </button>
</div>