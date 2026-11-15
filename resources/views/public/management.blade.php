<x-layouts.public title="Struktur Kepengurusan" description="Susunan pengurus {{ config('icmi.name') }} masa bakti {{ config('icmi.term') }}: ketua, wakil ketua, sekretaris, bendahara, dan bidang-bidang.">
    <section class="relative overflow-hidden bg-forest-950">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800"></div>
            <div class="absolute -left-24 -bottom-24 h-80 w-80 rounded-full bg-forest-500/25 blur-3xl animate-drift"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[['label' => 'Struktur Kepengurusan']]" class="[&_a]:text-forest-200 [&_span]:text-gold-300 [&_svg]:text-forest-400"/>
            <div class="mt-6 flex flex-wrap items-end justify-between gap-6">
                <div>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Struktur Kepengurusan</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-forest-100/80 sm:text-base">
                        Pengurus masa bakti {{ config('icmi.term') }} yang menggerakkan program organisasi.
                    </p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-gold-300">
                    Masa Bakti {{ config('icmi.term') }}
                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($core->isEmpty() && $divisions->isEmpty())
            <x-ui.empty-state title="Data pengurus belum tersedia"
                              description="Susunan kepengurusan masa bakti {{ config('icmi.term') }} akan ditampilkan setelah data resmi dikelola melalui CMS oleh Admin.">
                <x-ui.badge color="amber">Menunggu data resmi organisasi</x-ui.badge>
            </x-ui.empty-state>
        @else
            @if ($core->isNotEmpty())
                <div data-reveal>
                    <x-ui.section-heading eyebrow="Pimpinan" title="Pengurus Inti"/>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($core as $i => $member)
                            <div class="group rounded-3xl border border-forest-100 bg-white p-7 text-center shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-gold-300 hover:shadow-xl hover:shadow-forest-900/10" data-reveal style="animation-delay: {{ $i * 80 }}ms">
                                <div class="relative mx-auto h-24 w-24">
                                    <div class="absolute -inset-1.5 rounded-full bg-gradient-to-br from-gold-300 to-forest-300 opacity-60 transition group-hover:opacity-100" aria-hidden="true"></div>
                                    <x-ui.avatar :name="$member->name" :src="$member->photo?->url()" class="relative h-24 w-24 text-2xl"/>
                                </div>
                                <h3 class="mt-5 font-display text-lg font-bold text-forest-950">{{ $member->name }}</h3>
                                <p class="mt-1.5 inline-flex rounded-full bg-gold-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gold-700">{{ $member->position }}</p>
                                @if ($member->description)
                                    <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $member->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($divisions->isNotEmpty())
                <div class="mt-16 space-y-12">
                    @foreach ($divisions as $division => $members)
                        <div data-reveal>
                            <div class="flex items-center gap-4">
                                <h3 class="font-display text-xl font-bold text-forest-950">Bidang {{ $division }}</h3>
                                <span class="h-px flex-1 bg-gradient-to-r from-gold-300 to-transparent"></span>
                            </div>
                            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($members as $member)
                                    <div class="flex items-center gap-4 rounded-2xl border border-forest-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gold-300 hover:shadow-md">
                                        <x-ui.avatar :name="$member->name" :src="$member->photo?->url()" class="h-14 w-14 text-base"/>
                                        <div class="min-w-0">
                                            <p class="truncate font-display text-base font-bold text-forest-950">{{ $member->name }}</p>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">{{ $member->position }}</p>
                                            @if ($member->description)
                                                <p class="mt-1 text-xs leading-relaxed text-slate-500 line-clamp-2">{{ $member->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="mt-14 text-center text-xs text-slate-400" data-reveal>
                Data kepengurusan dikelola oleh Admin melalui CMS dan diperbarui sesuai keputusan organisasi.
            </p>
        @endif
    </section>
</x-layouts.public>