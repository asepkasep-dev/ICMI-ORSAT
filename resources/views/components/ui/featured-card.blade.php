@props(['article'])

<a href="{{ route('articles.show', $article) }}"
   {{ $attributes->merge(['class' => 'group grid overflow-hidden rounded-3xl border border-forest-100 bg-white shadow-sm transition-all duration-300 hover:border-gold-300 hover:shadow-xl hover:shadow-forest-900/10 lg:grid-cols-12']) }}>
    <div class="relative aspect-[16/9] overflow-hidden bg-forest-100 lg:col-span-7 lg:aspect-auto lg:min-h-[22rem]">
        @if ($article->featuredMedia)
            <img src="{{ $article->featuredMedia->url() }}" alt="{{ $article->featuredMedia->alt ?? $article->title }}"
                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-forest-800 to-forest-950">
                <x-logo class="h-20 w-20 opacity-40"/>
            </div>
        @endif
        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-gold-400 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-forest-950 shadow-sm">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
            Berita Unggulan
        </span>
    </div>
    <div class="flex flex-col justify-center p-7 sm:p-9 lg:col-span-5">
        <p class="flex flex-wrap items-center gap-2 text-[11px] font-medium uppercase tracking-wider text-slate-400">
            @if ($article->categories->first())
                <span class="font-bold text-gold-600">{{ $article->categories->first()->name }}</span>
                <span class="h-0.5 w-0.5 rounded-full bg-slate-300" aria-hidden="true"></span>
            @endif
            <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->translatedFormat('d F Y') }}</time>
        </p>
        <h3 class="mt-3 font-display text-2xl font-extrabold leading-tight tracking-tight text-forest-950 transition-colors group-hover:text-forest-600 sm:text-3xl">
            {{ $article->title }}
        </h3>
        @if ($article->excerpt)
            <p class="mt-4 text-sm leading-relaxed text-slate-500 line-clamp-3">{{ $article->excerpt }}</p>
        @endif
        <div class="mt-6 flex items-center gap-3">
            <x-ui.avatar :name="$article->author?->name ?? 'Penulis ICMI'" class="h-9 w-9 text-xs"/>
            <div>
                <p class="text-sm font-semibold text-forest-900">{{ $article->author?->name ?? 'Penulis ICMI' }}</p>
                <p class="text-xs text-slate-400">{{ $article->readingTime() }} menit baca</p>
            </div>
            <span class="ml-auto inline-flex items-center gap-2 text-sm font-semibold text-gold-600 transition group-hover:gap-3">
                Baca
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </span>
        </div>
    </div>
</a>