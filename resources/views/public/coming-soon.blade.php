<x-layouts.public :title="$title">
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-forest-100/70 blur-3xl"></div>
            <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-gold-100/60 blur-3xl"></div>
        </div>
        <div class="relative mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-32">
            <div class="animate-fade-up">
                <x-logo class="h-16 w-16"/>
            </div>
            <x-ui.badge color="gold" class="mt-8 animate-fade-up" style="animation-delay: 80ms">Dalam Pengembangan</x-ui.badge>
            <h1 class="mt-5 font-display text-4xl font-extrabold tracking-tight text-forest-950 animate-fade-up sm:text-5xl" style="animation-delay: 140ms">{{ $title }}</h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-500 animate-fade-up" style="animation-delay: 200ms">{{ $description }}</p>
            <p class="mt-3 text-sm text-slate-400 animate-fade-up" style="animation-delay: 240ms">
                Halaman ini merupakan bagian dari pengembangan bertahap website sesuai PRD.
            </p>
            <div class="mt-9 flex flex-wrap justify-center gap-3 animate-fade-up" style="animation-delay: 300ms">
                <x-ui.button :href="route('home')" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali ke Beranda
                </x-ui.button>
                <x-ui.button :href="route('contact')" variant="outline">Informasi Kontak</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>