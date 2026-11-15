@props(['title' => null, 'description' => null, 'ogImage' => null, 'canonical' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — '.config('icmi.short_name') : config('icmi.name').' — '.config('icmi.tagline') }}</title>
    <meta name="description" content="{{ $description ?? config('icmi.description') }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="{{ config('icmi.short_name') }}">
    <meta property="og:title" content="{{ $title ?? config('icmi.name') }}">
    <meta property="og:description" content="{{ $description ?? config('icmi.description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/icmi-logo.png') }}">
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <link rel="icon" href="{{ config('icmi.logo_url') ?? asset('images/icmi-emblem.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
    @php
        $navItems = [
            ['route' => 'home', 'label' => 'Beranda'],
            ['route' => 'profile', 'label' => 'Profil Organisasi'],
            ['route' => 'management', 'label' => 'Struktur Kepengurusan'],
            ['route' => 'articles.index', 'label' => 'Berita'],
            ['route' => 'gallery', 'label' => 'Galeri'],
            ['route' => 'contact', 'label' => 'Kontak'],
        ];
    @endphp

    <header x-data="{ mobileOpen: false }" class="fixed inset-x-0 top-0 z-50 border-b border-forest-100/80 bg-cream/85 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
                <x-logo class="h-9 w-9"/>
                <span class="leading-tight">
                    <span class="block font-display text-base font-extrabold tracking-tight text-forest-950"><x-brand-name accent="text-gold-600"/></span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-forest-500">Website Resmi</span>
                </span>
            </a>

            <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Navigasi utama">
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="relative rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $active ? 'text-forest-950' : 'text-slate-600 hover:bg-forest-50 hover:text-forest-800' }}"
                       @if($active) aria-current="page" @endif>
                        {{ $item['label'] }}
                        @if ($active)
                            <span class="absolute inset-x-3 -bottom-[13px] h-0.5 rounded-full bg-gold-400"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <x-ui.button :href="route('cms.dashboard')" variant="primary" class="px-4! py-2!">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        Dashboard
                    </x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="primary" class="px-4! py-2!">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                        Masuk
                    </x-ui.button>
                @endauth

                <button type="button" @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-forest-800 transition hover:bg-forest-50 lg:hidden" aria-label="Buka menu navigasi" :aria-expanded="mobileOpen">
                    <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="border-t border-forest-100 bg-cream/95 backdrop-blur-md lg:hidden">
            <nav class="space-y-1 px-4 py-4" aria-label="Navigasi mobile">
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" @click="mobileOpen = false"
                       class="flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium {{ $active ? 'bg-forest-900 text-white' : 'text-slate-700 hover:bg-forest-50' }}">
                        {{ $item['label'] }}
                        @if ($active)<span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>@endif
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="pt-16">
        @if (config('icmi.demo_mode'))
            <div class="border-b border-gold-500/30 bg-gold-400 px-4 py-2 text-center text-xs font-semibold text-forest-950">
                Mode pratinjau desain — konten contoh, bukan data resmi organisasi.
            </div>
        @endif
        @if (session('status'))
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
            </div>
        @endif
        {{ $slot }}
    </main>

    <footer class="mt-20 bg-forest-950 text-forest-100">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <div class="flex items-center gap-3">
                        <x-logo class="h-11 w-11"/>
                        <div>
                            <p class="font-display text-lg font-bold text-white">{{ config('icmi.short_name') }}</p>
                            <p class="text-xs text-forest-300">Masa Bakti {{ config('icmi.term') }}</p>
                        </div>
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-forest-200/80">{{ config('icmi.long_name') }}. {{ config('icmi.description') }}</p>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="font-display text-sm font-bold uppercase tracking-wider text-gold-400">Navigasi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($navItems as $item)
                            <li><a href="{{ route($item['route']) }}" class="text-forest-200/80 transition hover:text-gold-300">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="font-display text-sm font-bold uppercase tracking-wider text-gold-400">Kontak</h3>
                    <ul class="mt-4 space-y-3 text-sm text-forest-200/80">
                        <li class="flex gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400/80" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <span>{{ config('icmi.contact.address') ?? 'Alamat sekretariat — data resmi belum tersedia' }}</span>
                        </li>
                        <li class="flex gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400/80" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            <span>{{ config('icmi.contact.email') ?? 'Email organisasi — data resmi belum tersedia' }}</span>
                        </li>
                        <li class="flex gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400/80" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            <span>{{ config('icmi.contact.phone') ?? 'Nomor kontak — data resmi belum tersedia' }}</span>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="font-display text-sm font-bold uppercase tracking-wider text-gold-400">Media Sosial</h3>
                    <x-social-links dark :placeholders="false" class="mt-4">
                        <p class="mt-4 text-sm text-forest-200/60">Tautan media sosial resmi akan ditampilkan setelah data organisasi tersedia.</p>
                    </x-social-links>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-forest-300/70 sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('icmi.name') }}. Hak cipta dilindungi.</p>
                <p>Pusat Informasi &amp; Publikasi Resmi Organisasi</p>
            </div>
        </div>
    </footer>
    @livewireScripts
</body>
</html>