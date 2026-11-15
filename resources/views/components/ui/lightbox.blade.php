@props(['photos' => []])

<div x-data="lightbox(@js($photos))"
     x-on:lightbox-open.window="show($event.detail.index)"
     x-on:keydown.window.escape="if (open) close()"
     x-on:keydown.window.arrow-right="if (open) next()"
     x-on:keydown.window.arrow-left="if (open) prev()"
     x-effect="document.body.style.overflow = open ? 'hidden' : ''">
    <div x-show="open" x-cloak x-transition.opacity.duration.200ms
         class="fixed inset-0 z-[100] flex flex-col bg-forest-950/95 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Pratinjau foto">
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
            <p class="text-xs font-semibold tracking-wider text-forest-200" x-text="(index + 1) + ' / ' + photos.length"></p>
            <button type="button" x-on:click="close()" class="rounded-lg p-2 text-forest-200 transition hover:bg-white/10 hover:text-gold-300" aria-label="Tutup pratinjau">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="relative flex flex-1 items-center justify-center overflow-hidden px-14 pb-4 sm:px-20" x-on:click.self="close()">
            <button type="button" x-on:click="prev()" x-show="photos.length > 1"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full border border-white/15 bg-white/5 p-2.5 text-forest-100 transition hover:bg-gold-400 hover:text-forest-950 sm:left-5" aria-label="Foto sebelumnya">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <template x-if="current">
                <img :src="current.url" :alt="current.caption || 'Foto kegiatan'" class="max-h-full max-w-full rounded-xl object-contain shadow-2xl">
            </template>
            <button type="button" x-on:click="next()" x-show="photos.length > 1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full border border-white/15 bg-white/5 p-2.5 text-forest-100 transition hover:bg-gold-400 hover:text-forest-950 sm:right-5" aria-label="Foto berikutnya">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>

        <div class="border-t border-white/10 px-4 py-4 text-center sm:px-6">
            <p class="text-sm text-forest-100" x-text="current?.caption"></p>
            <template x-if="current?.article_url">
                <a :href="current.article_url" class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-gold-300 transition hover:text-gold-200">
                    <span x-text="'Dari berita: ' + current.article_title"></span>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </template>
        </div>
    </div>
</div>