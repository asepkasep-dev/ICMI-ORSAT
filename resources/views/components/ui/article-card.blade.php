@props(['article'])

<a href="{{ route('articles.show', $article) }}"
   {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-gold-300 hover:shadow-xl hover:shadow-forest-900/10']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-forest-100">
        @if ($article->featuredMedia)
            <img src="{{ $article->featuredMedia->url() }}" alt="{{ $article->featuredMedia->alt ?? $article->title }}"
                 loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-forest-800 to-forest-950">
                <x-logo class="h-14 w-14 opacity-40"/>
            </div>
        @endif
        @if ($article->categories->first())
            <span class="absolute left-3 top-3 rounded-full bg-forest-950/80 px-3 py-1 text-[11px] font-semibold text-gold-300 backdrop-blur-sm">
                {{ $article->categories->first()->name }}
            </span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-wider text-slate-400">
            <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->translatedFormat('d M Y') }}</time>
            <span class="h-0.5 w-0.5 rounded-full bg-slate-300" aria-hidden="true"></span>
            <span>{{ $article->readingTime() }} mnt baca</span>
        </p>
        <h3 class="mt-2.5 font-display text-lg font-bold leading-snug text-forest-950 line-clamp-2 transition-colors group-hover:text-forest-600">
            {{ $article->title }}
        </h3>
        @if ($article->excerpt)
            <p class="mt-2 text-sm leading-relaxed text-slate-500 line-clamp-2">{{ $article->excerpt }}</p>
        @endif
        <div class="mt-auto flex items-center gap-2.5 pt-4">
            <x-ui.avatar :name="$article->author?->name ?? 'Penulis ICMI'" class="h-7 w-7 text-[10px]"/>
            <span class="text-xs font-medium text-slate-600">{{ $article->author?->name ?? 'Penulis ICMI' }}</span>
            <svg class="ml-auto h-4 w-4 text-gold-500 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </div>
    </div>
</a>