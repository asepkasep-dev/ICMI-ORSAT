<x-layouts.cms :title="$title">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-3xl border border-forest-100 bg-white p-8 text-center shadow-sm sm:p-12">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-forest-50 text-forest-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/></svg>
            </span>
            <x-ui.badge color="gold" class="mt-6">{{ $milestone }}</x-ui.badge>
            <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-forest-950">{{ $title }}</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-500">{{ $description }}</p>
            <p class="mt-4 text-xs font-medium uppercase tracking-wider text-slate-400">Modul ini belum termasuk dalam Fase 1 — Project Foundation</p>
            <div class="mt-8">
                <x-ui.button :href="route('cms.dashboard')" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali ke Dashboard
                </x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.cms>