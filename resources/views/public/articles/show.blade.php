@php
    $shareUrl = route('articles.show', $article);
    $lightboxPhotos = collect();
    if ($article->featuredMedia) {
        $lightboxPhotos->push([
            'url' => $article->featuredMedia->url(),
            'caption' => $article->featuredMedia->caption ?? $article->featuredMedia->alt,
            'article_title' => $article->title,
            'article_url' => $shareUrl,
        ]);
    }
    foreach ($article->photos as $photo) {
        $lightboxPhotos->push([
            'url' => $photo->url(),
            'caption' => $photo->caption ?? $photo->alt,
            'article_title' => $article->title,
            'article_url' => $shareUrl,
        ]);
    }
@endphp

<x-layouts.public :title="$article->seo_title ?? $article->title"
                  :description="$article->seo_description ?? $article->excerpt"
                  :ogImage="$article->featuredMedia?->url()"
                  :canonical="$shareUrl">
    <article>
        <header class="mx-auto max-w-4xl px-4 pt-12 sm:px-6">
            <x-ui.breadcrumb :items="[['label' => 'Berita', 'url' => route('articles.index')], ['label' => \Illuminate\Support\Str::limit($article->title, 40)]]"/>
            <div class="mt-6 flex flex-wrap items-center gap-2">
                @foreach ($article->categories as $category)
                    <a href="{{ route('articles.index', ['category' => $category->slug]) }}">
                        <x-ui.badge color="gold">{{ $category->name }}</x-ui.badge>
                    </a>
                @endforeach
            </div>
            <h1 class="mt-5 font-display text-3xl font-extrabold leading-tight tracking-tight text-forest-950 sm:text-4xl lg:text-[2.75rem]">
                {{ $article->title }}
            </h1>
            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-4 border-y border-forest-100 py-4">
                <div class="flex items-center gap-3">
                    <x-ui.avatar :name="$article->author?->name ?? 'Penulis ICMI'" class="h-10 w-10 text-xs"/>
                    <div>
                        <p class="text-sm font-semibold text-forest-900">{{ $article->author?->name ?? 'Penulis ICMI' }}</p>
                        <p class="text-xs text-slate-400">Penulis</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->translatedFormat('d F Y') }}</time>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $article->readingTime() }} menit baca
                </div>
                <div class="ml-auto">
                    <x-ui.share-buttons :url="$shareUrl" :title="$article->title"/>
                </div>
            </div>
        </header>

        @if ($article->featuredMedia)
            <figure class="mx-auto mt-10 max-w-5xl px-4 sm:px-6" data-reveal>
                <button type="button" x-data x-on:click="$dispatch('lightbox-open', { index: 0 })"
                        class="group block w-full overflow-hidden rounded-3xl border border-forest-100 shadow-lg shadow-forest-900/10">
                    <img src="{{ $article->featuredMedia->url() }}" alt="{{ $article->featuredMedia->alt ?? $article->title }}"
                         class="aspect-[21/10] w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]">
                </button>
                @if ($article->featuredMedia->caption)
                    <figcaption class="mt-3 text-center text-xs text-slate-400">{{ $article->featuredMedia->caption }}</figcaption>
                @endif
            </figure>
        @endif

        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6" data-reveal>
            <x-ui.article-body :content="$article->content"/>
        </div>

        @if ($article->photos->isNotEmpty())
            <section class="mx-auto max-w-5xl px-4 pb-12 sm:px-6">
                <h2 class="font-display text-xl font-bold text-forest-950">Foto Pendukung</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($article->photos as $i => $photo)
                        <button type="button" x-data x-on:click="$dispatch('lightbox-open', { index: {{ $lightboxPhotos->count() - $article->photos->count() + $i }} })"
                                class="group relative aspect-[4/3] overflow-hidden rounded-2xl border border-forest-100 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mx-auto max-w-3xl px-4 pb-14 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-forest-900">Bagikan berita ini</p>
                <x-ui.share-buttons :url="$shareUrl" :title="$article->title"/>
            </div>
        </footer>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-forest-100 bg-forest-50/60">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <x-ui.section-heading eyebrow="Baca Lainnya" title="Berita Terkait" data-reveal/>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $item)
                        <div data-reveal style="animation-delay: {{ $i * 90 }}ms">
                            <x-ui.article-card :article="$item" class="h-full"/>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-ui.lightbox :photos="$lightboxPhotos->values()->all()"/>
</x-layouts.public>