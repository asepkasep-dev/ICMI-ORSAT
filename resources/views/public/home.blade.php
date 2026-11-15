<x-layouts.public>
    @php
        $organizationLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('icmi.name'),
            'alternateName' => config('icmi.long_name'),
            'url' => route('home'),
            'logo' => config('icmi.logo_url') ?? asset('images/icmi-logo.png'),
            'description' => config('icmi.description'),
            'email' => config('icmi.contact.email'),
            'telephone' => config('icmi.contact.phone'),
            'address' => config('icmi.contact.address'),
            'sameAs' => array_values(array_filter(config('icmi.social', []))) ?: null,
        ]);
    @endphp
    <x-slot:head>
        <script type="application/ld+json">@json($organizationLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
    </x-slot:head>
    @php
        $lightboxPhotos = $galleryPhotos->map(fn ($m) => [
            'url' => $m->url(),
            'caption' => $m->caption ?? $m->alt,
            'article_title' => $m->article_title,
            'article_url' => route('articles.show', $m->article_slug),
        ])->values()->all();
        $messageParagraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $profile->chairman_message))));
    @endphp

    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -left-40 top-[-8rem] h-[30rem] w-[30rem] rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
            <div class="absolute right-[-6rem] top-1/3 h-96 w-96 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
            <div class="absolute bottom-[-10rem] left-1/3 h-96 w-96 rounded-full bg-forest-400/15 blur-3xl animate-drift"></div>
            <svg class="absolute inset-0 h-full w-full opacity-[0.05]" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="hero-stars" width="80" height="80" patternUnits="userSpaceOnUse">
                        <g fill="none" stroke="#D5B46A" stroke-width="1">
                            <rect x="27" y="27" width="26" height="26"/>
                            <rect x="27" y="27" width="26" height="26" transform="rotate(45 40 40)"/>
                        </g>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#hero-stars)"/>
            </svg>
        </div>

        <div class="relative mx-auto grid max-w-7xl gap-14 px-4 pb-24 pt-20 sm:px-6 lg:grid-cols-12 lg:items-center lg:gap-10 lg:pb-32 lg:pt-28 lg:px-8">
            <div class="lg:col-span-7">
                <div class="animate-fade-up">
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-gold-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                        Website Resmi · Masa Bakti {{ config('icmi.term') }}
                    </span>
                </div>
                <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.1] tracking-tight text-white animate-fade-up sm:text-5xl xl:text-6xl" style="animation-delay: 90ms">
                    ICMI Kecamatan<br>
                    <span class="text-gold-400">Caringin</span>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-forest-100/85 animate-fade-up sm:text-lg" style="animation-delay: 180ms">
                    {{ config('icmi.long_name') }}. Pusat informasi dan publikasi digital resmi
                    untuk profil organisasi, struktur kepengurusan, berita, dan kegiatan.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-3 animate-fade-up" style="animation-delay: 270ms">
                    <x-ui.button :href="route('articles.index')" variant="gold" class="px-6! py-3!">
                        Jelajahi Berita
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </x-ui.button>
                    <x-ui.button :href="route('profile')" variant="light" class="px-6! py-3!">Profil Organisasi</x-ui.button>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-sm animate-fade-in lg:ml-auto" style="animation-delay: 300ms">
                    <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-gold-400/20 via-transparent to-forest-400/20 blur-xl" aria-hidden="true"></div>
                    <div class="relative rounded-[2rem] border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
                        <div class="flex justify-center">
                            <x-logo class="h-28 w-28 drop-shadow-[0_8px_24px_rgba(213,180,106,0.25)]"/>
                        </div>
                        <p class="mt-6 text-center font-display text-lg font-bold text-white">{{ config('icmi.short_name') }}</p>
                        <p class="mt-1 text-center text-xs uppercase tracking-[0.2em] text-gold-300/90">{{ \Illuminate\Support\Str::after(config('icmi.name'), ' ') }}</p>
                        <div class="mt-6 grid grid-cols-3 divide-x divide-white/10 rounded-2xl border border-white/10 bg-forest-950/40 py-4 text-center">
                            <div>
                                <p class="font-display text-xl font-extrabold text-gold-400">{{ $stats['articles'] }}</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-forest-200/70">Berita Terbit</p>
                            </div>
                            <div>
                                <p class="font-display text-xl font-extrabold text-gold-400">{{ $stats['categories'] }}</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-forest-200/70">Kategori</p>
                            </div>
                            <div>
                                <p class="font-display text-xl font-extrabold text-gold-400">{{ $stats['photos'] }}</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-forest-200/70">Foto Kegiatan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative border-t border-white/10 bg-forest-950/60">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-10 gap-y-2 px-4 py-4 text-xs font-semibold uppercase tracking-[0.16em] text-forest-200/60 sm:px-6 lg:px-8">
                <span>Profil Organisasi</span>
                <span class="hidden h-1 w-1 rounded-full bg-gold-400/60 sm:block" aria-hidden="true"></span>
                <span>Berita &amp; Kegiatan</span>
                <span class="hidden h-1 w-1 rounded-full bg-gold-400/60 sm:block" aria-hidden="true"></span>
                <span>Galeri Dokumentasi</span>
                <span class="hidden h-1 w-1 rounded-full bg-gold-400/60 sm:block" aria-hidden="true"></span>
                <span>Informasi Kontak</span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12" data-reveal>
            <div class="lg:col-span-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">Sambutan</p>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-forest-950 sm:text-4xl">Sambutan Ketua</h2>
                @if ($profile->chairman_message)
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">
                        Pesan Ketua {{ config('icmi.name') }} masa bakti {{ config('icmi.term') }}
                        untuk anggota dan masyarakat.
                    </p>
                    <x-ui.button :href="route('profile').'#sambutan'" variant="outline" class="mt-6">
                        Baca sambutan lengkap
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </x-ui.button>
                @else
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">
                        Sambutan resmi Ketua {{ config('icmi.name') }} masa bakti {{ config('icmi.term') }}
                        akan dimuat di halaman ini setelah naskah resmi tersedia dan disetujui.
                    </p>
                    <x-ui.badge color="amber" class="mt-5">Menunggu data resmi organisasi</x-ui.badge>
                @endif
            </div>
            <div class="lg:col-span-7">
                <div class="relative overflow-hidden rounded-3xl border border-forest-100 bg-white p-8 shadow-sm sm:p-10">
                    <svg class="absolute right-6 top-6 h-16 w-16 text-gold-200" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/></svg>
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :name="$profile->chairman_name ?? 'Ketua ICMI'" :src="$profile->chairmanPhoto?->url()" class="h-16 w-16 text-xl"/>
                        <div>
                            <p class="font-display text-base font-bold text-forest-950">{{ $profile->chairman_name ?? 'Ketua '.config('icmi.name') }}</p>
                            <p class="text-sm text-slate-500">Masa Bakti {{ config('icmi.term') }}</p>
                        </div>
                    </div>
                    @if ($profile->chairman_message)
                        <div class="mt-6 space-y-4 border-l-2 border-gold-400 pl-5">
                            @foreach (array_slice($messageParagraphs, 0, 2) as $paragraph)
                                <p class="text-sm italic leading-relaxed text-slate-600">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-6 border-l-2 border-gold-400 pl-5 text-sm italic leading-relaxed text-slate-500">
                            “Naskah sambutan Ketua sedang disiapkan oleh pengurus. Konten resmi akan
                            ditampilkan di sini setelah mendapat persetujuan pihak yang berwenang.”
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-forest-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-ui.section-heading eyebrow="Tentang Kami" title="Mengenal Organisasi"
                                  :description="$profile->vision ?? 'Sejarah, visi, misi, dan tujuan '.config('icmi.name').' dipublikasikan melalui halaman profil setelah dokumen resmi organisasi tersedia.'"
                                  data-reveal/>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['title' => 'Visi', 'desc' => $profile->vision ?: 'Arah dan cita-cita organisasi masa bakti '.config('icmi.term').'.', 'icon' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                    ['title' => 'Misi', 'desc' => $profile->missionItems() ? $profile->missionItems()[0].' '.count($profile->missionItems()).' misi organisasi selengkapnya di halaman profil.' : 'Langkah dan upaya nyata untuk mewujudkan visi organisasi.', 'icon' => 'M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z'],
                    ['title' => 'Tujuan', 'desc' => $profile->goalItems() ? $profile->goalItems()[0].' '.count($profile->goalItems()).' tujuan organisasi selengkapnya di halaman profil.' : 'Target dan manfaat yang ingin dicapai bagi anggota dan masyarakat.', 'icon' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 16.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Z M12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z'],
                ] as $i => $item)
                    <div class="group rounded-3xl border border-forest-100 bg-cream p-7 transition-all duration-300 hover:-translate-y-1 hover:border-gold-300 hover:shadow-lg hover:shadow-forest-900/5" data-reveal style="animation-delay: {{ $i * 90 }}ms">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-forest-900 text-gold-400 transition-colors group-hover:bg-gold-400 group-hover:text-forest-950">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        </span>
                        <h3 class="mt-5 font-display text-xl font-bold text-forest-950">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 line-clamp-4">{{ $item['desc'] }}</p>
                        @if (!$profile->vision)
                            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-gold-600">Konten resmi sedang disiapkan</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-10" data-reveal>
                <x-ui.button :href="route('profile')" variant="outline">
                    Selengkapnya tentang organisasi
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-ui.section-heading eyebrow="Publikasi" title="Berita & Kegiatan" data-reveal>
            <x-ui.button :href="route('articles.index')" variant="ghost">
                Lihat semua berita
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </x-ui.button>
        </x-ui.section-heading>

        @if ($featured)
            <div class="mt-10" data-reveal>
                <x-ui.featured-card :article="$featured"/>
            </div>
        @endif

        @if ($latest->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $i => $article)
                    <div data-reveal style="animation-delay: {{ $i * 90 }}ms">
                        <x-ui.article-card :article="$article" class="h-full"/>
                    </div>
                @endforeach
            </div>
        @elseif (!$featured)
            <div class="mt-10" data-reveal>
                <x-ui.empty-state title="Belum ada berita yang diterbitkan"
                                  description="Berita dan kegiatan organisasi akan tampil di sini setelah diterbitkan melalui CMS oleh Admin atau Writer.">
                </x-ui.empty-state>
            </div>
        @endif

        @if ($categories->isNotEmpty())
            <div class="mt-10 flex flex-wrap items-center gap-2" data-reveal>
                <span class="mr-2 text-xs font-bold uppercase tracking-wider text-slate-400">Kategori</span>
                @foreach ($categories as $cat)
                    <a href="{{ route('categories.show', $cat) }}"
                       class="rounded-full border border-forest-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-gold-400 hover:bg-gold-50 hover:text-forest-900">
                        {{ $cat->name }} <span class="ml-1 text-slate-400">{{ $cat->articles_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="border-y border-forest-100 bg-forest-50/60">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-ui.section-heading eyebrow="Dokumentasi" title="Galeri Foto" data-reveal>
                <x-ui.button :href="route('gallery')" variant="ghost">
                    Buka galeri
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </x-ui.button>
            </x-ui.section-heading>
            @if ($galleryPhotos->isNotEmpty())
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal>
                    @foreach ($galleryPhotos as $i => $photo)
                        <button type="button" x-data x-on:click="$dispatch('lightbox-open', { index: {{ $i }} })"
                                class="group relative aspect-[4/3] overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <span class="absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-forest-950/90 to-forest-950/0 px-4 pb-3 pt-8 text-left text-xs font-medium text-white transition-transform duration-300 group-hover:translate-y-0">
                                {{ $photo->caption }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="flex aspect-[4/3] flex-col items-center justify-center rounded-2xl border border-dashed border-forest-200 bg-white/70 text-forest-300">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.4" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                            <p class="mt-2 text-xs font-medium text-forest-400">Foto kegiatan</p>
                        </div>
                    @endfor
                </div>
                <p class="mt-6 text-center text-sm text-slate-500" data-reveal>
                    Galeri menampilkan foto dari berita yang telah diterbitkan. Dokumentasi akan hadir setelah berita pertama dipublikasikan.
                </p>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] bg-forest-950" data-reveal>
            <div class="relative grid gap-10 p-8 sm:p-12 lg:grid-cols-2 lg:items-center">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
                    <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
                </div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-400">Hubungi Kami</p>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white">Informasi Kontak</h2>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-forest-200/80">
                        Alamat sekretariat, email, dan nomor kontak resmi organisasi akan
                        ditampilkan setelah data resmi tersedia.
                    </p>
                    <x-ui.button :href="route('contact')" variant="gold" class="mt-7">Halaman Kontak</x-ui.button>
                </div>
                <div class="relative space-y-4">
                    @foreach ([
                        ['label' => 'Sekretariat', 'value' => config('icmi.contact.address'), 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                        ['label' => 'Email Resmi', 'value' => config('icmi.contact.email'), 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
                        ['label' => 'Nomor Kontak', 'value' => config('icmi.contact.phone'), 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
                    ] as $contact)
                        <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 px-5 py-4 backdrop-blur-sm">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold-400/15 text-gold-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $contact['icon'] }}"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-forest-300">{{ $contact['label'] }}</p>
                                <p class="truncate text-sm font-medium text-white">{{ $contact['value'] ?? 'Data resmi belum tersedia' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-ui.lightbox :photos="$lightboxPhotos"/>
</x-layouts.public>