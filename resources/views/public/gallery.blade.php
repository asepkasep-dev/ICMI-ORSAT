@php
    $lightboxPhotos = $photos->map(fn ($m) => [
        'url' => $m->url(),
        'caption' => $m->caption ?? $m->alt,
        'article_title' => $m->article_title,
        'article_url' => route('articles.show', $m->article_slug),
    ])->values()->all();
@endphp

<x-layouts.public title="Galeri Foto" description="Dokumentasi foto kegiatan {{ config('icmi.name') }} yang bersumber dari berita yang telah diterbitkan.">
    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[['label' => 'Galeri']]" class="[&_a]:text-forest-200 [&_span]:text-gold-300 [&_svg]:text-forest-400"/>
            <div class="mt-6 flex flex-wrap items-end justify-between gap-6">
                <div>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Galeri Foto</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-forest-100/80 sm:text-base">
                        Dokumentasi kegiatan organisasi, diambil dari foto berita yang telah diterbitkan.
                        Klik foto untuk memperbesar.
                    </p>
                </div>
                @if ($photos->isNotEmpty())
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-gold-300">
                        {{ $photos->count() }} foto
                    </span>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        @if ($photos->isEmpty())
            <x-ui.empty-state title="Galeri masih kosong"
                              description="Foto akan tampil di sini setelah berita dengan dokumentasi kegiatan diterbitkan melalui CMS.">
            </x-ui.empty-state>
        @else
            <div class="columns-2 gap-4 sm:columns-3 lg:columns-4" data-reveal>
                @foreach ($photos as $i => $photo)
                    <button type="button" x-data x-on:click="$dispatch('lightbox-open', { index: {{ $i }} })"
                            class="group relative mb-4 block w-full break-inside-avoid overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-forest-900/10">
                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy"
                             class="w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <span class="absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-forest-950/95 via-forest-950/70 to-transparent px-4 pb-3 pt-10 text-left transition-transform duration-300 group-hover:translate-y-0">
                            <span class="block text-xs font-medium leading-snug text-white">{{ $photo->caption }}</span>
                            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wider text-gold-300">{{ $photo->article_title }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <x-ui.lightbox :photos="$lightboxPhotos"/>
</x-layouts.public>