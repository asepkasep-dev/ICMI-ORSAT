<x-layouts.public title="Profil Organisasi" description="Sejarah, visi, misi, tujuan, dan sambutan Ketua ICMI Kecamatan Caringin masa bakti {{ config('icmi.term') }}.">
    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-gold-500/15 blur-3xl animate-drift-slow"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[['label' => 'Profil Organisasi']]" class="[&_a]:text-forest-200 [&_span]:text-gold-300 [&_svg]:text-forest-400"/>
            <div class="mt-6 flex flex-wrap items-center gap-6">
                <x-logo class="h-20 w-20"/>
                <div>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Profil Organisasi</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-forest-100/80 sm:text-base">{{ config('icmi.long_name') }}</p>
                </div>
                <span class="ml-auto inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-gold-300">
                    Masa Bakti {{ config('icmi.term') }}
                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12" data-reveal>
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-24">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">Sejarah</p>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-forest-950">Jejak Langkah Organisasi</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">
                        Dari wadah cendekiawan nasional hingga gerakan nyata di tingkat kecamatan.
                    </p>
                </div>
            </div>
            <div class="lg:col-span-8">
                @if ($profile->history)
                    <div class="rounded-3xl border border-forest-100 bg-white p-8 shadow-sm sm:p-10">
                        <x-ui.article-body :content="$profile->history"/>
                    </div>
                @else
                    <x-ui.empty-state title="Sejarah resmi sedang disiapkan"
                                      description="Naskah sejarah organisasi akan dimuat setelah dokumen resmi tersedia dan disetujui.">
                        <x-ui.badge color="amber">Menunggu data resmi organisasi</x-ui.badge>
                    </x-ui.empty-state>
                @endif
            </div>
        </div>
    </section>

    <section class="border-y border-forest-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-12" data-reveal>
                <div class="lg:col-span-5">
                    <div class="relative h-full overflow-hidden rounded-3xl bg-forest-950 p-8 sm:p-10">
                        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-gold-500/20 blur-3xl animate-drift-slow"></div>
                        </div>
                        <p class="relative text-xs font-bold uppercase tracking-[0.2em] text-gold-400">Visi</p>
                        @if ($profile->vision)
                            <p class="relative mt-5 font-display text-2xl font-bold leading-snug text-white">{{ $profile->vision }}</p>
                        @else
                            <p class="relative mt-5 font-display text-2xl font-bold leading-snug text-white/60">Visi resmi organisasi sedang disiapkan.</p>
                        @endif
                        <div class="relative mt-8 h-1 w-16 rounded-full bg-gold-400"></div>
                    </div>
                </div>
                <div class="lg:col-span-7">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">Misi</p>
                    @if ($profile->missionItems())
                        <ol class="mt-6 space-y-4">
                            @foreach ($profile->missionItems() as $i => $mission)
                                <li class="flex gap-4 rounded-2xl border border-forest-100 bg-cream p-5 transition hover:border-gold-300" data-reveal style="animation-delay: {{ $i * 70 }}ms">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-forest-900 font-display text-sm font-bold text-gold-400">{{ $i + 1 }}</span>
                                    <p class="text-sm leading-relaxed text-slate-600">{{ $mission }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <x-ui.empty-state class="mt-6" title="Misi resmi sedang disiapkan"
                                          description="Rumusan misi organisasi akan dimuat setelah dokumen resmi tersedia.">
                            <x-ui.badge color="amber">Menunggu data resmi organisasi</x-ui.badge>
                        </x-ui.empty-state>
                    @endif
                </div>
            </div>

            <div class="mt-14" data-reveal>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">Tujuan</p>
                @if ($profile->goalItems())
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        @foreach ($profile->goalItems() as $i => $goal)
                            <div class="flex gap-4 rounded-2xl border border-forest-100 bg-cream p-6 transition hover:-translate-y-0.5 hover:border-gold-300 hover:shadow-md" data-reveal style="animation-delay: {{ $i * 70 }}ms">
                                <svg class="h-5 w-5 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <p class="text-sm leading-relaxed text-slate-600">{{ $goal }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state class="mt-6" title="Tujuan resmi sedang disiapkan"
                                      description="Rumusan tujuan organisasi akan dimuat setelah dokumen resmi tersedia.">
                        <x-ui.badge color="amber">Menunggu data resmi organisasi</x-ui.badge>
                    </x-ui.empty-state>
                @endif
            </div>
        </div>
    </section>

    <section id="sambutan" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12" data-reveal>
            <div class="lg:col-span-4">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600">Sambutan</p>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-forest-950">Sambutan Ketua</h2>
                <div class="mt-8 flex items-center gap-4">
                    <x-ui.avatar :name="$profile->chairman_name ?? 'Ketua ICMI'" :src="$profile->chairmanPhoto?->url()" class="h-20 w-20 text-2xl"/>
                    <div>
                        <p class="font-display text-lg font-bold text-forest-950">{{ $profile->chairman_name ?? 'Ketua ICMI Kecamatan Caringin' }}</p>
                        <p class="text-sm text-slate-500">Masa Bakti {{ config('icmi.term') }}</p>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-8">
                @if ($profile->chairman_message)
                    <div class="relative overflow-hidden rounded-3xl border border-forest-100 bg-white p-8 shadow-sm sm:p-10">
                        <svg class="absolute right-6 top-6 h-16 w-16 text-gold-200" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/></svg>
                        <x-ui.article-body :content="$profile->chairman_message" class="relative italic text-slate-600"/>
                    </div>
                @else
                    <x-ui.empty-state title="Naskah sambutan sedang disiapkan"
                                      description="Sambutan Ketua akan dimuat setelah naskah resmi tersedia dan disetujui pihak yang berwenang.">
                        <x-ui.badge color="amber">Menunggu data resmi organisasi</x-ui.badge>
                    </x-ui.empty-state>
                @endif
            </div>
        </div>
    </section>
</x-layouts.public>