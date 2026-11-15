@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — CMS {{ config('icmi.short_name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-cream">
    @php
        $user = auth()->user();
        $initials = collect(explode(' ', trim($user->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
    @endphp

    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-forest-950/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        <aside x-cloak class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-forest-950 transition-transform duration-300 ease-out lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               aria-label="Sidebar CMS">
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
                <x-logo class="h-9 w-9"/>
                <div class="leading-tight">
                    <p class="font-display text-sm font-extrabold tracking-tight text-white">ICMI <span class="text-gold-400">Caringin</span></p>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-forest-300">Content Management</p>
                </div>
                <button type="button" @click="sidebarOpen = false" class="ml-auto rounded-lg p-1.5 text-forest-300 transition hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup sidebar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="cms-sidebar-nav flex-1 space-y-7 overflow-y-auto px-4 py-6">
                <div class="space-y-1">
                    <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-forest-400">Utama</p>
                    <x-cms.nav-link route="cms.dashboard" label="Dashboard" icon="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                </div>

                <div class="space-y-1">
                    <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-forest-400">Konten</p>
                    <x-cms.nav-link route="cms.articles" label="Manajemen Berita" icon="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/>
                    <x-cms.nav-link route="cms.categories" label="Kategori Berita" icon="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z"/>
                    <x-cms.nav-link route="cms.media" label="Media Library" icon="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </div>

                <div class="space-y-1">
                    <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-forest-400">Organisasi</p>
                    <x-cms.nav-link route="cms.profile" label="Profil Organisasi" icon="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"/>
                    <x-cms.nav-link route="cms.management" label="Struktur Pengurus" icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </div>

                @if ($user->isAdmin())
                    <div class="space-y-1">
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-forest-400">Administrasi</p>
                        <x-cms.nav-link route="cms.users" label="Manajemen Pengguna" icon="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/>
                        <x-cms.nav-link route="cms.settings" label="Pengaturan Website" icon="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </div>
                @endif
            </nav>

            <div class="shrink-0 border-t border-white/10 p-4">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="mb-3 flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-forest-200/70 transition hover:bg-forest-900 hover:text-gold-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                    Lihat Website Publik
                </a>
                <div class="flex items-center gap-3 rounded-xl bg-forest-900/70 p-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold-400 font-display text-xs font-bold text-forest-950">{{ $initials }}</span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                        <p class="text-[11px] font-medium text-gold-300/90">{{ $user->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-forest-300 transition hover:bg-white/10 hover:text-red-300" title="Keluar" aria-label="Keluar dari CMS">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col lg:pl-72">
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-4 border-b border-forest-100 bg-white/85 px-4 backdrop-blur-md sm:px-6 lg:px-8">
                <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-forest-800 transition hover:bg-forest-50 lg:hidden" aria-label="Buka sidebar">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <div class="min-w-0 leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-forest-400">CMS {{ config('icmi.short_name') }}</p>
                    <h1 class="truncate font-display text-lg font-bold text-forest-950">{{ $title }}</h1>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <x-ui.badge :color="$user->isAdmin() ? 'gold' : 'forest'">{{ $user->role->label() }}</x-ui.badge>
                    <span class="hidden text-sm font-medium text-slate-600 sm:block">{{ $user->name }}</span>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    @if (session('status'))
                        <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
                    @endif
                    @if (session('error'))
                        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>