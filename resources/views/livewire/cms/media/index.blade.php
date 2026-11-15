<div class="space-y-6">
    <x-cms.page-header title="Media Library"
                       :description="auth()->user()->isAdmin() ? 'Kelola seluruh gambar website. Gambar dioptimasi ke WebP otomatis saat diunggah.' : 'Kelola gambar unggahan Anda. Gambar dioptimasi ke WebP otomatis saat diunggah.'"/>

    <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
        <div class="relative">
            <x-ui.file-upload name="uploads" label="Unggah gambar baru" :multiple="true" wire:model="uploads"
                              :hint="\App\Support\MediaStorage::hint().' Bisa pilih hingga '.config('icmi.uploads.max_files').' file sekaligus.'"/>
            <div wire:loading wire:target="uploads"
                 class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 rounded-xl bg-white/90 backdrop-blur-sm">
                <svg class="h-8 w-8 animate-spin text-forest-600" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
                <p class="text-sm font-semibold text-forest-800">Mengunggah &amp; mengoptimasi gambar…</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-0 flex-1 sm:max-w-md">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari nama file atau teks alternatif…"
                       aria-label="Cari media"
                       class="w-full rounded-xl border border-forest-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
            </div>
            <p class="ml-auto text-sm font-medium text-slate-500">{{ $total }} gambar</p>
        </div>
    </div>

    @if ($items->isEmpty())
        <x-ui.empty-state title="Belum ada gambar"
                          description="Unggah gambar pertama Anda melalui panel di atas, atau ubah kata kunci pencarian."/>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            @foreach ($items as $item)
                <button type="button" wire:click="openDetail({{ $item->id }})" wire:key="media-{{ $item->id }}"
                        class="group overflow-hidden rounded-2xl border border-forest-100 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:border-forest-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-500"
                        aria-label="Buka detail {{ $item->file_name }}">
                    <span class="relative block aspect-square overflow-hidden bg-forest-50">
                        <img src="{{ $item->url() }}" alt="{{ $item->alt }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <span class="absolute inset-0 flex items-center justify-center bg-forest-950/0 opacity-0 transition group-hover:bg-forest-950/30 group-hover:opacity-100">
                            <span class="rounded-lg bg-white/95 px-2.5 py-1 text-xs font-semibold text-forest-900 shadow">Lihat Detail</span>
                        </span>
                    </span>
                    <span class="block px-3 py-2.5">
                        <span class="block truncate text-xs font-semibold text-forest-950">{{ $item->file_name }}</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400">{{ $item->width }}×{{ $item->height }} · {{ \Illuminate\Support\Number::fileSize($item->file_size, precision: 1) }}</span>
                    </span>
                </button>
            @endforeach
        </div>

        {{ $items->links('components.pagination') }}
    @endif

    @if ($detail)
        <div x-data x-show="$wire.detailId !== null" x-cloak
             class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
             role="dialog" aria-modal="true" aria-label="Detail media">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-forest-100 bg-white shadow-2xl"
                 x-on:click.outside="$wire.closeDetail()">
                <div class="flex items-start justify-between gap-4 border-b border-forest-100 p-5">
                    <div class="min-w-0">
                        <h3 class="truncate font-display text-lg font-bold text-forest-950">{{ $detail->file_name }}</h3>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Diunggah {{ $detail->created_at->translatedFormat('d M Y H:i') }}
                            @if ($detail->uploader) oleh {{ $detail->uploader->name }} @endif
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-forest-50 hover:text-forest-900" aria-label="Tutup detail">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid gap-6 p-5 md:grid-cols-2">
                    <div>
                        <img src="{{ $detail->url() }}" alt="{{ $detail->alt }}" class="w-full rounded-xl border border-forest-100 object-cover">
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-lg bg-forest-50 px-3 py-2">
                                <dt class="font-semibold uppercase tracking-wide text-forest-500">Dimensi</dt>
                                <dd class="mt-0.5 font-medium text-forest-950">{{ $detail->width }}×{{ $detail->height }} px</dd>
                            </div>
                            <div class="rounded-lg bg-forest-50 px-3 py-2">
                                <dt class="font-semibold uppercase tracking-wide text-forest-500">Ukuran</dt>
                                <dd class="mt-0.5 font-medium text-forest-950">{{ \Illuminate\Support\Number::fileSize($detail->file_size, precision: 1) }}</dd>
                            </div>
                            <div class="rounded-lg bg-forest-50 px-3 py-2">
                                <dt class="font-semibold uppercase tracking-wide text-forest-500">Format</dt>
                                <dd class="mt-0.5 font-medium text-forest-950">{{ $detail->mime_type }}</dd>
                            </div>
                            <div class="rounded-lg bg-forest-50 px-3 py-2">
                                <dt class="font-semibold uppercase tracking-wide text-forest-500">Penggunaan</dt>
                                <dd class="mt-0.5 font-medium text-forest-950">{{ $detail->usageCount() }} konten</dd>
                            </div>
                        </dl>
                        <div x-data="{ copied: false }" class="mt-3">
                            <button type="button"
                                    x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($detail->url()) }}).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-forest-200 px-4 py-2.5 text-sm font-semibold text-forest-800 transition hover:bg-forest-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                <span x-show="!copied">Salin URL Gambar</span>
                                <span x-show="copied" x-cloak class="text-forest-600">URL tersalin!</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-5">
                        @if ($usages->isNotEmpty())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-amber-800">Digunakan oleh</p>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($usages as $usage)
                                        <li class="text-xs leading-relaxed text-amber-900">
                                            <span class="font-semibold">{{ $usage['type'] }}:</span> {{ $usage['title'] }}
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-[11px] leading-relaxed text-amber-700">Media yang masih digunakan tidak dapat dihapus.</p>
                            </div>
                        @endif

                        @can('update', $detail)
                            <form wire:submit="saveDetail" class="space-y-4">
                                <x-ui.input name="alt" label="Teks alternatif (alt)" wire:model="alt"
                                            placeholder="Deskripsi gambar untuk aksesibilitas"/>
                                <x-ui.textarea name="caption" label="Caption (opsional)" rows="3" wire:model="caption"
                                               placeholder="Keterangan gambar…" hint="Maks. 500 karakter."/>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    @can('delete', $detail)
                                        <x-ui.button type="button" variant="danger" wire:click="confirmDelete({{ $detail->id }})"
                                                     :disabled="$detail->usageCount() > 0"
                                                     title="{{ $detail->usageCount() > 0 ? 'Media masih digunakan' : 'Hapus media' }}">
                                            Hapus
                                        </x-ui.button>
                                    @endcan
                                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" class="ml-auto">
                                        <span wire:loading.remove wire:target="saveDetail">Simpan Metadata</span>
                                        <span wire:loading wire:target="saveDetail">Menyimpan…</span>
                                    </x-ui.button>
                                </div>
                            </form>
                        @else
                            <div class="rounded-xl bg-forest-50 p-4 text-xs leading-relaxed text-forest-700">
                                Anda dapat melihat media ini karena terkait berita Anda, tetapi hanya pemilik atau Admin yang dapat mengubah metadata.
                            </div>
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Teks alternatif</dt>
                                    <dd class="mt-0.5 text-forest-950">{{ $detail->alt ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Caption</dt>
                                    <dd class="mt-0.5 text-forest-950">{{ $detail->caption ?: '—' }}</dd>
                                </div>
                            </dl>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div x-show="$wire.deleteId !== null" x-cloak
         class="fixed inset-0 z-[95] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Konfirmasi hapus media">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.set('deleteId', null)">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-forest-950">Hapus media ini?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.deleteName"></span>” akan dihapus permanen dari Media Library beserta file fisiknya.
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="$set('deleteId', null)">Batal</x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="deleteConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteConfirmed">Ya, hapus</span>
                    <span wire:loading wire:target="deleteConfirmed">Menghapus…</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>