@php
    $user = auth()->user();
    $milestones = [
        ['no' => 1, 'name' => 'Project Foundation', 'desc' => 'Setup TALL stack, database, login, role, layout dasar.', 'done' => true],
        ['no' => 2, 'name' => 'UI/UX Implementation', 'desc' => 'Design system dan desain seluruh halaman utama.', 'done' => true],
        ['no' => 3, 'name' => 'Content Management System', 'desc' => 'CRUD berita, editor, kategori, media, workflow publikasi.', 'done' => true],
        ['no' => 4, 'name' => 'Organization Profile', 'desc' => 'Sejarah, visi misi, sambutan, pengurus, pengaturan.', 'done' => true],
        ['no' => 5, 'name' => 'Public Website & SEO', 'desc' => 'Integrasi konten publik, pencarian, galeri, SEO.', 'done' => true],
        ['no' => 6, 'name' => 'Testing & Deployment', 'desc' => 'Pengujian, backup/pemulihan, panduan deployment dan pelatihan.', 'done' => true],
    ];
    $activityMeta = static fn (string $action): array => match (true) {
        str_contains($action, 'published') => ['bg' => 'bg-gold-100 text-gold-700', 'path' => 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'],
        str_contains($action, 'scheduled') => ['bg' => 'bg-sky-50 text-sky-600', 'path' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        str_contains($action, 'revision') => ['bg' => 'bg-red-50 text-red-500', 'path' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
        str_contains($action, 'review') => ['bg' => 'bg-amber-50 text-amber-600', 'path' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z'],
        str_contains($action, 'archived') || str_contains($action, 'restored') => ['bg' => 'bg-slate-100 text-slate-500', 'path' => 'm20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z'],
        str_contains($action, 'created') => ['bg' => 'bg-forest-50 text-forest-600', 'path' => 'M12 4.5v15m7.5-7.5h-15'],
        str_contains($action, 'updated') => ['bg' => 'bg-sky-50 text-sky-600', 'path' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z'],
        str_contains($action, 'deleted') => ['bg' => 'bg-red-50 text-red-500', 'path' => 'M6 18 18 6M6 6l12 12'],
        default => ['bg' => 'bg-slate-100 text-slate-500', 'path' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    };
@endphp

<div class="space-y-6">
    <div class="relative overflow-hidden rounded-3xl bg-forest-950 p-7 sm:p-9">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
            <div class="absolute -bottom-24 left-1/4 h-64 w-64 rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
        </div>
        <div class="relative flex flex-wrap items-center justify-between gap-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-400">{{ $user->role->label() }} Dashboard</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Selamat datang, {{ $user->name }}
                </h2>
                <p class="mt-2 max-w-xl text-sm text-forest-200/80">
                    @if ($user->isAdmin())
                        Anda memiliki akses penuh sebagai super admin: kelola berita, kategori, media,
                        profil organisasi, pengguna, dan pengaturan website.
                    @else
                        Anda dapat menulis, mengedit, dan menerbitkan berita milik sendiri,
                        serta mengajukan berita untuk diperiksa Admin.
                    @endif
                </p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-center backdrop-blur-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-forest-300">Masa Bakti</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-gold-400">{{ config('icmi.term') }}</p>
            </div>
        </div>
    </div>

    {{-- Perlu perhatian: antrean review (Admin) / revisi (Writer) --}}
    @if ($user->isAdmin() && $articleStats['in_review'] > 0)
        <a href="{{ route('cms.articles', ['status' => 'in_review']) }}"
           class="flex flex-wrap items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 transition hover:border-amber-300 hover:bg-amber-100/70">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-semibold text-amber-900">
                {{ $articleStats['in_review'] }} berita menunggu review Anda.
                <span class="font-normal text-amber-800">Periksa, terbitkan, atau kembalikan untuk revisi.</span>
            </span>
            <span class="text-sm font-bold text-amber-700">Review sekarang &rarr;</span>
        </a>
    @elseif ($user->isWriter() && $articleStats['revision'] > 0)
        <a href="{{ route('cms.articles', ['status' => 'revision']) }}"
           class="flex flex-wrap items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 transition hover:border-red-300 hover:bg-red-100/70">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-500 text-white">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </span>
            <span class="min-w-0 flex-1 text-sm font-semibold text-red-900">
                {{ $articleStats['revision'] }} berita Anda dikembalikan untuk revisi.
                <span class="font-normal text-red-800">Baca catatan Admin, perbaiki, lalu terbitkan kembali.</span>
            </span>
            <span class="text-sm font-bold text-red-700">Perbaiki sekarang &rarr;</span>
        </a>
    @endif

    {{-- Statistik berita (PRD §5.3) — angka dari database aktual --}}
    <div>
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-forest-700">
                {{ $user->isAdmin() ? 'Statistik Berita' : 'Statistik Berita Anda' }}
            </h3>
            <a href="{{ route('cms.articles') }}" class="text-xs font-semibold text-forest-600 transition hover:text-forest-900">
                Kelola berita &rarr;
            </a>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <x-ui.stat-card :label="$user->isAdmin() ? 'Total Berita' : 'Berita Saya'" :value="$articleStats['total']">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg>
            </x-ui.stat-card>
            <x-ui.stat-card label="Terbit" :value="$articleStats['published']" accent="gold">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </x-ui.stat-card>
            <x-ui.stat-card label="Draf" :value="$articleStats['draft']" accent="gray">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
            </x-ui.stat-card>
            <x-ui.stat-card label="Menunggu Review" :value="$articleStats['in_review']" accent="amber">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </x-ui.stat-card>
            @if ($user->isAdmin())
                <x-ui.stat-card label="Terjadwal" :value="$articleStats['scheduled']" accent="sky">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </x-ui.stat-card>
            @else
                <x-ui.stat-card label="Perlu Revisi" :value="$articleStats['revision']" accent="red">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </x-ui.stat-card>
            @endif
        </div>
    </div>

    @if ($user->isWriter())
        <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h3 class="font-display text-lg font-bold text-forest-950">Kewenangan Writer</h3>
                <x-ui.badge color="forest">PRD v2.1</x-ui.badge>
            </div>
            <div class="mt-5 grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-forest-600">Dapat dilakukan</p>
                    <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                        @foreach ([
                            'Membuat dan mengedit berita milik sendiri',
                            'Menerbitkan berita milik sendiri',
                            'Mengajukan berita untuk diperiksa Admin',
                            'Mengunggah gambar berita',
                            'Memperbaiki berita yang dikembalikan untuk revisi',
                        ] as $ability)
                            <li class="flex gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-forest-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                {{ $ability }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-red-500">Tidak dapat dilakukan</p>
                    <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                        @foreach ([
                            'Menjadwalkan berita',
                            'Mengedit atau menghapus berita penulis lain',
                            'Mengelola kategori dan profil organisasi',
                            'Mengelola akun pengguna dan pengaturan website',
                        ] as $limitation)
                            <li class="flex gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                {{ $limitation }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="min-w-0 space-y-6 lg:col-span-7">
            @if ($user->isAdmin())
                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="font-display text-lg font-bold text-forest-950">Aktivitas Terbaru</h3>
                        <x-ui.badge color="gray">{{ $recentActivity->count() }} tercatat</x-ui.badge>
                    </div>
                    @if ($recentActivity->isEmpty())
                        <p class="mt-5 text-sm text-slate-500">Belum ada aktivitas tercatat. Aktivitas penulisan dan publikasi berita akan muncul di sini.</p>
                    @else
                        <ol class="mt-5 space-y-1">
                            @foreach ($recentActivity as $log)
                                @php $meta = $activityMeta($log->action); @endphp
                                <li class="flex gap-3.5 rounded-xl px-2 py-2.5 transition hover:bg-forest-50/60">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $meta['bg'] }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['path'] }}"/></svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm leading-snug text-forest-950">{{ $log->description ?? $log->action }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $log->user?->name ?? 'Sistem' }} &middot; {{ $log->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            @else
                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="font-display text-lg font-bold text-forest-950">Berita Terbaru Anda</h3>
                        <a href="{{ route('cms.articles') }}" class="text-xs font-semibold text-forest-600 transition hover:text-forest-900">Lihat semua &rarr;</a>
                    </div>
                    @if ($recentArticles->isEmpty())
                        <div class="mt-5">
                            <x-ui.empty-state title="Belum ada berita"
                                              description="Mulai tulis berita pertama Anda untuk melihatnya di sini.">
                                <x-ui.button :href="route('cms.articles.create')" variant="primary">Tulis Berita</x-ui.button>
                            </x-ui.empty-state>
                        </div>
                    @else
                        <ul class="mt-5 space-y-2">
                            @foreach ($recentArticles as $article)
                                @php
                                    $href = auth()->user()->can('update', $article)
                                        ? route('cms.articles.edit', $article)
                                        : ($article->isPublished() ? route('articles.show', $article) : route('cms.articles'));
                                @endphp
                                <li wire:key="recent-{{ $article->id }}">
                                    <a href="{{ $href }}"
                                       class="flex items-center gap-3.5 rounded-xl border border-transparent px-2 py-2.5 transition hover:border-forest-100 hover:bg-forest-50/60">
                                        @if ($article->featuredMedia)
                                            <img src="{{ $article->featuredMedia->url() }}" alt="" class="h-12 w-16 shrink-0 rounded-lg object-cover">
                                        @else
                                            <span class="flex h-12 w-16 shrink-0 items-center justify-center rounded-lg bg-forest-100 text-forest-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                                            </span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-forest-950">{{ $article->title }}</p>
                                            <p class="mt-0.5 text-xs text-slate-400">Diperbarui {{ $article->updated_at->diffForHumans() }}</p>
                                        </div>
                                        <x-cms.status-badge :status="$article->status" class="shrink-0"/>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="font-display text-lg font-bold text-forest-950">Progres Pengembangan</h3>
                    <x-ui.badge color="gold">Milestone 6 selesai</x-ui.badge>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">Kesiapan teknis dan panduan selesai. Deployment dan pelatihan langsung menunggu hosting serta domain resmi.</p>
                <ol class="mt-6 space-y-0">
                    @foreach ($milestones as $m)
                        <li class="relative flex gap-4 pb-6 last:pb-0">
                            @if (! $loop->last)
                                <span class="absolute left-[15px] top-8 h-[calc(100%-1.5rem)] w-px {{ $m['done'] ? 'bg-gold-400' : 'bg-forest-100' }}" aria-hidden="true"></span>
                            @endif
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $m['done'] ? 'bg-gold-400 text-forest-950' : 'border border-forest-200 bg-cream text-forest-400' }}">
                                @if ($m['done'])
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @else
                                    {{ $m['no'] }}
                                @endif
                            </span>
                            <div class="min-w-0 pt-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-display text-sm font-bold {{ $m['done'] ? 'text-forest-950' : 'text-slate-600' }}">Milestone {{ $m['no'] }} — {{ $m['name'] }}</p>
                                    @if ($m['done'])
                                        <x-ui.badge color="forest" class="px-2! py-0.5! text-[10px]!">Selesai</x-ui.badge>
                                    @elseif ($m['no'] === 4)
                                        <x-ui.badge color="amber" class="px-2! py-0.5! text-[10px]!">Berikutnya</x-ui.badge>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $m['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="min-w-0 space-y-6 lg:col-span-5">
            @if ($user->isAdmin())
                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                    <h3 class="font-display text-lg font-bold text-forest-950">Aksi Cepat</h3>
                    <div class="mt-4 space-y-2.5">
                        <x-ui.button :href="route('cms.articles.create')" variant="primary" class="w-full justify-start!">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tulis Berita Baru
                        </x-ui.button>
                        <x-ui.button :href="route('cms.articles')" variant="outline" class="w-full justify-start!">
                            <svg class="h-4 w-4 text-forest-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg>
                            Manajemen Berita
                        </x-ui.button>
                        <x-ui.button :href="route('cms.articles', ['status' => 'in_review'])" variant="outline" class="w-full justify-start!">
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Review Berita
                            @if ($articleStats['in_review'] > 0)
                                <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $articleStats['in_review'] }}</span>
                            @endif
                        </x-ui.button>
                        <x-ui.button :href="route('cms.media')" variant="outline" class="w-full justify-start!">
                            <svg class="h-4 w-4 text-forest-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                            Media Library
                        </x-ui.button>
                        <x-ui.button :href="route('home')" variant="ghost" class="w-full justify-start!">
                            <svg class="h-4 w-4 text-forest-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            Lihat Website Publik
                        </x-ui.button>
                    </div>
                </div>

                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="font-display text-lg font-bold text-forest-950">Ringkasan Pengguna</h3>
                        <a href="{{ route('cms.users') }}" class="text-xs font-semibold text-forest-600 transition hover:text-forest-900">Kelola &rarr;</a>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        @foreach ([
                            ['Total', $userStats['total_users'], 'text-forest-950'],
                            ['Admin', $userStats['admins'], 'text-gold-600'],
                            ['Writer', $userStats['writers'], 'text-sky-600'],
                            ['Nonaktif', $userStats['inactive'], 'text-red-500'],
                        ] as [$label, $value, $color])
                            <div class="rounded-xl border border-forest-100 bg-cream/60 px-4 py-3">
                                <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 font-display text-2xl font-bold {{ $color }}">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @else
                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                    <h3 class="font-display text-lg font-bold text-forest-950">Mulai Menulis</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Tulis berita dengan editor rich text, lengkapi gambar, lalu terbitkan sendiri
                        atau ajukan untuk diperiksa Admin.
                    </p>
                    <x-ui.button :href="route('cms.articles.create')" variant="primary" class="mt-5 w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        Tulis Berita Baru
                    </x-ui.button>
                    <x-ui.button :href="route('cms.articles')" variant="outline" class="mt-2.5 w-full">
                        Lihat Berita Saya
                    </x-ui.button>
                </div>
            @endif

            <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm">
                <h3 class="font-display text-lg font-bold text-forest-950">Informasi Sistem</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    @foreach ([
                        ['Laravel', app()->version()],
                        ['Livewire', \Composer\InstalledVersions::getPrettyVersion('livewire/livewire')],
                        ['PHP', PHP_VERSION],
                        ['Database', strtoupper(config('database.default'))],
                        ['Environment', ucfirst(app()->environment())],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between gap-4 border-b border-forest-50 pb-2.5 last:border-0 last:pb-0">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="font-semibold text-forest-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</div>