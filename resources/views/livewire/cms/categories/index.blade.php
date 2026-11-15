<div class="space-y-6">
    <x-cms.page-header title="Kategori Berita"
                       description="Kelola kategori untuk mengelompokkan berita. Kategori yang masih digunakan harus dipindahkan beritanya terlebih dahulu sebelum dihapus.">
        <x-ui.button type="button" variant="primary" wire:click="openCreate">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Kategori
        </x-ui.button>
    </x-cms.page-header>

    <div class="rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
        <div class="relative max-w-md">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari nama atau slug kategori…"
                   aria-label="Cari kategori"
                   class="w-full rounded-xl border border-forest-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-forest-100 text-left">
                <thead class="bg-forest-50/80">
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-forest-600">
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Deskripsi</th>
                        <th class="px-5 py-3.5">Jumlah Berita</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-forest-50">
                    @forelse ($categories as $cat)
                        <tr class="transition hover:bg-forest-50/50" wire:key="category-{{ $cat->id }}">
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-forest-950">{{ $cat->name }}</p>
                                <p class="text-xs text-slate-400">/kategori/{{ $cat->slug }}</p>
                            </td>
                            <td class="max-w-xs px-5 py-4 text-sm text-slate-500">
                                {{ $cat->description ? \Illuminate\Support\Str::limit($cat->description, 90) : '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ui.badge :color="$cat->articles_count > 0 ? 'forest' : 'gray'">{{ $cat->articles_count }} berita</x-ui.badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="openEdit({{ $cat->id }})"
                                            class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900" title="Edit" aria-label="Edit {{ $cat->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $cat->id }})"
                                            class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 hover:text-red-700" title="Hapus" aria-label="Hapus {{ $cat->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10">
                                <x-ui.empty-state title="Belum ada kategori"
                                                  description="Buat kategori pertama untuk mengelompokkan berita, atau ubah kata kunci pencarian.">
                                    <x-ui.button type="button" variant="primary" wire:click="openCreate">Tambah Kategori</x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="$wire.formOpen" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Form kategori">
        <div class="w-full max-w-lg rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.closeForm()">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display text-lg font-bold text-forest-950" x-text="$wire.editingId ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
                    <p class="mt-1 text-sm text-slate-500">Slug dibuat otomatis dari nama dan dapat disesuaikan.</p>
                </div>
                <button type="button" wire:click="closeForm" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-forest-50 hover:text-forest-900" aria-label="Tutup form">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <x-ui.input name="name" label="Nama kategori" wire:model.live.debounce.300ms="name" placeholder="Contoh: Kegiatan Organisasi"/>
                <div>
                    <label for="category-slug" class="mb-1.5 block text-sm font-medium text-forest-900">Slug URL</label>
                    <div class="flex items-center overflow-hidden rounded-xl border border-forest-200 bg-white shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                        <span class="border-r border-forest-100 bg-forest-50 px-3 py-2.5 text-sm text-slate-400">/kategori/</span>
                        <input type="text" id="category-slug" wire:model.live="slug"
                               class="w-full border-0 bg-transparent px-3 py-2.5 text-sm text-ink focus:outline-none focus:ring-0"
                               placeholder="slug-otomatis">
                    </div>
                    @error('slug') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                </div>
                <x-ui.textarea name="description" label="Deskripsi (opsional)" rows="3" wire:model="description"
                               placeholder="Deskripsi singkat kategori…" hint="Maks. 255 karakter."/>
                <div class="flex justify-end gap-2 pt-2">
                    <x-ui.button type="button" variant="outline" wire:click="closeForm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save" x-text="$wire.editingId ? 'Simpan Perubahan' : 'Buat Kategori'"></span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="$wire.deleteId !== null" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Konfirmasi hapus kategori">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.set('deleteId', null)">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-forest-950">Hapus kategori ini?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.deleteName"></span>” akan dihapus permanen dari daftar kategori.
            </p>

            <div x-show="$wire.deleteCount > 0" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    <span x-text="$wire.deleteCount"></span> berita masih menggunakan kategori ini.
                </p>
                <p class="mt-1 text-xs leading-relaxed text-amber-800">
                    Pilih kategori tujuan terlebih dahulu. Berita akan dipindahkan otomatis sebelum kategori dihapus.
                </p>
                <div class="mt-3">
                    <x-ui.select name="moveTo" label="Pindahkan berita ke" wire:model="moveTo">
                        <option value="">— Pilih kategori tujuan —</option>
                        @foreach ($moveOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="$set('deleteId', null)">Batal</x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="deleteConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteConfirmed" x-text="$wire.deleteCount > 0 ? 'Pindahkan & Hapus' : 'Ya, hapus'"></span>
                    <span wire:loading wire:target="deleteConfirmed">Menghapus…</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>