@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-xs font-medium text-slate-500">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} hasil
        </p>
        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-forest-100 bg-white text-slate-300" aria-disabled="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </span>
            @else
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-forest-200 bg-white text-forest-700 transition hover:border-forest-400 hover:bg-forest-50" aria-label="Halaman sebelumnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-sm text-slate-400" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-forest-900 px-2 text-sm font-bold text-gold-300" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})" wire:key="page-{{ $page }}"
                                    class="flex h-9 min-w-9 items-center justify-center rounded-lg border border-forest-200 bg-white px-2 text-sm font-medium text-forest-700 transition hover:border-forest-400 hover:bg-forest-50"
                                    aria-label="Halaman {{ $page }}">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-forest-200 bg-white text-forest-700 transition hover:border-forest-400 hover:bg-forest-50" aria-label="Halaman berikutnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-forest-100 bg-white text-slate-300" aria-disabled="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif