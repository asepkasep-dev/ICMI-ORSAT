<x-layouts.public title="Kontak" description="Alamat sekretariat, email, nomor kontak resmi, dan media sosial ICMI Kecamatan Caringin.">
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
                        Tautan media sosial resmi organisasi akan ditampilkan setelah data tersedia.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach ([
                        ['label' => 'Instagram', 'value' => config('icmi.social.instagram'), 'icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069Zm0 1.802c-3.153 0-3.507.012-4.746.068-2.695.123-3.995 1.44-4.118 4.118-.056 1.239-.067 1.593-.067 4.746 0 3.153.011 3.507.067 4.746.123 2.678 1.423 3.995 4.118 4.118 1.239.056 1.593.068 4.746.068 3.153 0 3.507-.012 4.746-.068 2.695-.123 3.995-1.44 4.118-4.118.056-1.239.068-1.593.068-4.746 0-3.153-.012-3.507-.068-4.746-.123-2.678-1.423-3.995-4.118-4.118-1.239-.056-1.593-.068-4.746-.068Zm0 3.064a4.971 4.971 0 1 1 0 9.942 4.971 4.971 0 0 1 0-9.942Zm0 8.168a3.197 3.197 0 1 0 0-6.394 3.197 3.197 0 0 0 0 6.394Zm6.406-8.367a1.162 1.162 0 1 1-2.324 0 1.162 1.162 0 0 1 2.324 0Z'],
                        ['label' => 'Facebook', 'value' => config('icmi.social.facebook'), 'icon' => 'M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.19 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.9 3.78-3.9 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.75 8.44-4.92 8.44-9.94Z'],
                        ['label' => 'YouTube', 'value' => config('icmi.social.youtube'), 'icon' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z'],
                        ['label' => 'TikTok', 'value' => config('icmi.social.tiktok'), 'icon' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07Z'],
                    ] as $social)
                        @if ($social['value'])
                            <a href="{{ $social['value'] }}" target="_blank" rel="noopener noreferrer"
                               class="flex h-11 w-11 items-center justify-center rounded-full border border-forest-200 bg-white text-forest-700 transition hover:border-forest-500 hover:bg-forest-900 hover:text-gold-300"
                               aria-label="{{ $social['label'] }} resmi" title="{{ $social['label'] }}">
                                <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $social['icon'] }}"/></svg>
                            </a>
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-dashed border-forest-200 bg-forest-50 text-forest-300"
                                  aria-label="{{ $social['label'] }} — belum tersedia" title="{{ $social['label'] }} — data resmi belum tersedia">
                                <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $social['icon'] }}"/></svg>
                            </span>
                        @endif
                    @endforeach
                </div>
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