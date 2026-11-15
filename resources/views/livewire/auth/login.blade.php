<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-forest-950 lg:flex lg:flex-col lg:justify-between">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-forest-600/30 blur-3xl animate-drift"></div>
            <div class="absolute -bottom-24 right-0 h-80 w-80 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
            <svg class="absolute inset-0 h-full w-full opacity-[0.05]" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="star-pattern" width="72" height="72" patternUnits="userSpaceOnUse">
                        <g fill="none" stroke="#D5B46A" stroke-width="1">
                            <rect x="24" y="24" width="24" height="24"/>
                            <rect x="24" y="24" width="24" height="24" transform="rotate(45 36 36)"/>
                        </g>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#star-pattern)"/>
            </svg>
        </div>

        <div class="relative z-10 p-10 xl:p-14">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <x-logo class="h-11 w-11"/>
                <span class="leading-tight">
                    <span class="block font-display text-lg font-extrabold text-white"><x-brand-name accent="text-gold-400"/></span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-forest-300">Website Resmi</span>
                </span>
            </a>
        </div>

        <div class="relative z-10 p-10 xl:p-14">
            <x-ui.badge color="gold" class="bg-gold-400/15! text-gold-300! ring-gold-400/30!">Masa Bakti {{ config('icmi.term') }}</x-ui.badge>
            <h2 class="mt-6 max-w-md font-display text-4xl font-extrabold leading-tight tracking-tight text-white xl:text-[2.75rem]">
                Pusat Informasi &amp; Publikasi <span class="text-gold-400">Digital Resmi</span> Organisasi
            </h2>
            <p class="mt-5 max-w-md text-sm leading-relaxed text-forest-200/80">
                Kelola profil organisasi, struktur kepengurusan, berita, dan kegiatan
                {{ config('icmi.name') }} melalui satu dashboard yang aman dan mudah digunakan.
            </p>
        </div>

        <div class="relative z-10 border-t border-white/10 p-10 xl:p-14">
            <p class="text-xs text-forest-300/70">&copy; {{ date('Y') }} {{ config('icmi.name') }}</p>
        </div>
    </div>

    <div class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24">
        <div class="mx-auto w-full max-w-md animate-fade-up">
            <div class="mb-10 flex items-center gap-3 lg:hidden">
                <x-logo class="h-10 w-10"/>
                <div class="leading-tight">
                    <p class="font-display text-base font-extrabold text-forest-950"><x-brand-name accent="text-gold-600"/></p>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-forest-500">Content Management System</p>
                </div>
            </div>

            <h1 class="font-display text-3xl font-extrabold tracking-tight text-forest-950">Masuk ke CMS</h1>
            <p class="mt-2 text-sm text-slate-500">Gunakan akun yang diberikan oleh Admin untuk mengelola konten website.</p>

            <form wire:submit="authenticate" class="mt-8 space-y-5" novalidate>
                @if ($errors->has('email'))
                    <x-ui.alert type="error">{{ $errors->first('email') }}</x-ui.alert>
                @endif

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-forest-900">Email</label>
                    <input wire:model.blur="email" type="email" id="email" name="email" autocomplete="username" required autofocus
                           class="block w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25"
                           placeholder="nama@organisasi.or.id">
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="password" class="mb-1.5 block text-sm font-medium text-forest-900">Password</label>
                    <div class="relative">
                        <input wire:model="password" :type="showPassword ? 'text' : 'password'" id="password" name="password" autocomplete="current-password" required
                               class="block w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 pr-12 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25"
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 transition hover:text-forest-700"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="submit" variant="primary" class="w-full py-3!" wire:loading.attr="disabled" wire:target="authenticate">
                    <span wire:loading.remove wire:target="authenticate">Masuk</span>
                    <span wire:loading wire:target="authenticate" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
                        Memeriksa…
                    </span>
                </x-ui.button>
            </form>

            <p class="mt-6 text-center text-xs leading-relaxed text-slate-400">
                Tidak tersedia registrasi publik. Akun Admin dan Writer dibuat oleh pengelola organisasi.
            </p>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-forest-700 transition hover:text-forest-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali ke website publik
                </a>
            </div>
        </div>
    </div>
</div>