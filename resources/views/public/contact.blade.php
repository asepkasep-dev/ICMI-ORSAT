<x-layouts.public title="Kontak" description="Alamat sekretariat, email, nomor kontak resmi, dan media sosial {{ config('icmi.name') }}.">
    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -left-24 -bottom-24 h-80 w-80 rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[['label' => 'Kontak']]" class="[&_a]:text-forest-200 [&_span]:text-gold-300 [&_svg]:text-forest-400"/>
            <h1 class="mt-6 font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Kontak</h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-forest-100/80 sm:text-base">
                Kanal komunikasi resmi {{ config('icmi.name') }} untuk masyarakat dan mitra organisasi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([
                ['label' => 'Sekretariat', 'value' => config('icmi.contact.address'), 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z', 'note' => 'Alamat sekretariat resmi akan ditampilkan setelah data organisasi tersedia.'],
                ['label' => 'Email Resmi', 'value' => config('icmi.contact.email'), 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75', 'note' => 'Email organisasi akan ditampilkan setelah ditetapkan oleh pengurus.'],
                ['label' => 'Nomor Kontak', 'value' => config('icmi.contact.phone'), 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z', 'note' => 'Nomor kontak resmi akan ditampilkan setelah ditetapkan oleh pengurus.'],
            ] as $i => $contact)
                <div class="rounded-3xl border border-forest-100 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-gold-300 hover:shadow-lg" data-reveal style="animation-delay: {{ $i * 90 }}ms">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-forest-900 text-gold-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $contact['icon'] }}"/></svg>
                    </span>
                    <h2 class="mt-5 font-display text-lg font-bold text-forest-950">{{ $contact['label'] }}</h2>
                    @if ($contact['value'])
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $contact['value'] }}</p>
                    @else
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $contact['note'] }}</p>
                        <x-ui.badge color="amber" class="mt-4">Menunggu data resmi</x-ui.badge>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-14 rounded-3xl border border-forest-100 bg-white p-8 shadow-sm sm:p-10" data-reveal>
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div>
                    <h2 class="font-display text-xl font-bold text-forest-950">Media Sosial</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
                        Ikuti kanal resmi organisasi. Ikon bergaris putus menandakan tautan yang belum tersedia.
                    </p>
                </div>
                <x-social-links />
            </div>
        </div>

        <div class="mt-10 flex items-start gap-3 rounded-2xl border border-gold-300/60 bg-gold-50 px-5 py-4" data-reveal>
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
            <p class="text-sm leading-relaxed text-forest-800">
                Sesuai kebijakan organisasi, informasi kontak hanya dimuat setelah data resmi ditetapkan
                dan disetujui pengurus. Pengelolaan kontak dilakukan oleh Admin melalui CMS.
            </p>
        </div>
    </section>
</x-layouts.public>