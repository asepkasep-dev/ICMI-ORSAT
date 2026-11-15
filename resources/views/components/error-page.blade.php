@props([
    'code',
    'title',
    'description',
])

<x-layouts.public :title="$code.' — '.$title">
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-forest-100/70 blur-3xl"></div>
            <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-gold-100/60 blur-3xl"></div>
        </div>
        <div class="relative mx-auto flex max-w-2xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-32">
            <p class="font-display text-7xl font-extrabold tracking-tight text-forest-200 sm:text-8xl">{{ $code }}</p>
            <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-forest-950 sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-500">{{ $description }}</p>
            <div class="mt-9 flex flex-wrap justify-center gap-3">
                <x-ui.button :href="route('home')" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    Kembali ke Beranda
                </x-ui.button>
                @auth
                    <x-ui.button :href="route('cms.dashboard')" variant="outline">Ke Dashboard</x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="outline">Masuk ke CMS</x-ui.button>
                @endauth
            </div>
        </div>
    </section>
</x-layouts.public>