<div>
    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
            <div class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-14 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[['label' => 'Berita']]" class="[&_a]:text-forest-200 [&_span]:text-gold-300 [&_svg]:text-forest-400"/>
            <div class="mt-6 flex flex-wrap items-end justify-between gap-8">
                <div class="max-w-xl">
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Berita &amp; Artikel</h1>
                    <p class="mt-4 text-sm leading-relaxed text-forest-100/80 sm:text-base">
                        Publikasi resmi kegiatan, program, dan gagasan ICMI Kecamatan Caringin.
                    </p>
                </div>
                <div class="relative w-full max-w-md">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-forest-200/60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari judul atau isi berita…"
                           aria-label="Cari berita"
                           class="w-full rounded-xl border border-white/15 bg-white/10 py-3 pl-11 pr-4 text-sm text-white placeholder-forest-200/50 backdrop-blur transition focus:border-gold-400/60 focus:outline-none focus:ring-2 focus:ring-gold-400/25">
                    <div wire:loading wire:target="q" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="h-4 w-4 animate-spin text-gold-300" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Filter kategori">
                <button type="button" wire:click="setCategory('')"
                        class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $category === '' ? 'bg-forest-900 text-gold-300 shadow-sm' : 'border border-forest-200 bg-white text-slate-600 hover:border-forest-400 hover:text-forest-800' }}">
                    Semua
                </button>
                @foreach ($categories as $cat)
                    <button type="button" wire:click="setCategory('{{ $cat->slug }}')"
                            class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $category === $cat->slug ? 'bg-forest-900 text-gold-300 shadow-sm' : 'border border-forest-200 bg-white text-slate-600 hover:border-forest-400 hover:text-forest-800' }}">
                        {{ $cat->name }}
                        <span class="ml-1 {{ $category === $cat->slug ? 'text-gold-400/80' : 'text-slate-400' }}">{{ $cat->articles_count }}</span>
                    </button>
                @endforeach
            </div>
            <div class="w-44">
                <x-ui.select name="sort" wire:model.live="sort" aria-label="Urutkan berita">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="title">Judul A–Z</option>
                </x-ui.select>
            </div>
        </div>

        @if ($featured)
            <div class="mt-8" data-reveal>
                <x-ui.featured-card :article="$featured"/>
            </div>
        @endif

        <div wire:loading.class="opacity-50 pointer-events-none" wire:target="q,category,sort,gotoPage,previousPage,nextPage"
             class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 transition-opacity duration-200">
            @forelse ($articles as $article)
                <x-ui.article-card :article="$article"/>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-ui.empty-state title="Tidak ada berita yang cocok"
                                      description="Coba kata kunci lain atau hapus filter untuk melihat seluruh berita.">
                        <x-ui.button type="button" wire:click="resetFilters" variant="outline">Hapus filter</x-ui.button>
                    </x-ui.empty-state>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $articles->links('components.pagination') }}
        </div>
    </section>
</div>