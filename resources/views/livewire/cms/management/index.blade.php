<div class="space-y-6">
    <x-cms.page-header title="Struktur Pengurus"
                       description="Kelola data pengurus yang tampil di halaman Struktur Kepengurusan. Pengurus tanpa bidang ditampilkan sebagai Pengurus Inti.">
        <x-ui.button href="{{ route('management') }}" target="_blank" variant="outline">Lihat halaman publik</x-ui.button>
        <x-ui.button type="button" variant="primary" wire:click="openCreate">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Pengurus
        </x-ui.button>
    </x-cms.page-header>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
        <div class="relative w-full max-w-md">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari nama, jabatan, atau bidang…"
                   aria-label="Cari pengurus"
                   class="w-full rounded-xl border border-forest-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
        </div>
        <p class="text-sm text-slate-500">{{ $activeCount }} dari {{ $total }} pengurus tampil di website</p>
    </div>

    @forelse ($groups as $division => $members)
        <div class="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm" wire:key="group-{{ md5($division) }}">
            <div class="flex items-center justify-between gap-4 border-b border-forest-100 bg-forest-50/80 px-5 py-3">
                <h2 class="font-display text-sm font-bold text-forest-950">{{ $division === '' ? 'Pengurus Inti' : 'Bidang '.$division }}</h2>
                <span class="text-xs text-slate-500">{{ $members->count() }} orang</span>
            </div>
            <ul class="divide-y divide-forest-50">
                @foreach ($members as $member)
                    <li class="flex flex-wrap items-center gap-4 px-5 py-3.5 transition hover:bg-forest-50/50 {{ $member->is_active ? '' : 'opacity-60' }}" wire:key="member-{{ $member->id }}">
                        @if ($q === '')
                            <div class="flex flex-col">
                                <button type="button" wire:click="move({{ $member->id }}, 'up')" @disabled($loop->first)
                                        class="rounded p-0.5 text-forest-500 transition hover:bg-forest-100 hover:text-forest-900 disabled:pointer-events-none disabled:opacity-25"
                                        aria-label="Naikkan urutan {{ $member->name }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                </button>
                                <button type="button" wire:click="move({{ $member->id }}, 'down')" @disabled($loop->last)
                                        class="rounded p-0.5 text-forest-500 transition hover:bg-forest-100 hover:text-forest-900 disabled:pointer-events-none disabled:opacity-25"
                                        aria-label="Turunkan urutan {{ $member->name }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                            </div>
                        @endif
                        <x-ui.avatar :name="$member->name" :src="$member->photo?->url()" class="h-11 w-11 text-sm"/>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-forest-950">{{ $member->name }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gold-600">{{ $member->position }}</p>
                        </div>
                        <span class="hidden text-xs text-slate-400 sm:inline">Urutan {{ $member->sort_order }}</span>
                        @if ($member->is_active)
                            <x-ui.badge color="forest">Tampil</x-ui.badge>
                        @else
                            <x-ui.badge color="gray">Disembunyikan</x-ui.badge>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="toggleActive({{ $member->id }})"
                                    class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900"
                                    title="{{ $member->is_active ? 'Sembunyikan' : 'Tampilkan' }}" aria-label="{{ $member->is_active ? 'Sembunyikan' : 'Tampilkan' }} {{ $member->name }}">
                                @if ($member->is_active)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                @endif
                            </button>
                            <button type="button" wire:click="openEdit({{ $member->id }})"
                                    class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900" title="Edit" aria-label="Edit {{ $member->name }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $member->id }})"
                                    class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 hover:text-red-700" title="Hapus" aria-label="Hapus {{ $member->name }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <div class="rounded-2xl border border-forest-100 bg-white p-10 shadow-sm">
            <x-ui.empty-state :title="$q === '' ? 'Belum ada data pengurus' : 'Pengurus tidak ditemukan'"
                              :description="$q === '' ? 'Tambahkan pengurus sesuai SK kepengurusan resmi. Halaman publik menampilkan placeholder sampai data tersedia.' : 'Ubah kata kunci pencarian.'">
                @if ($q === '')
                    <x-ui.button type="button" variant="primary" wire:click="openCreate">Tambah Pengurus</x-ui.button>
                @endif
            </x-ui.empty-state>
        </div>
    @endforelse

    <div x-show="$wire.formOpen" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center overflow-y-auto bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Form pengurus">
        <div class="my-auto w-full max-w-2xl rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display text-lg font-bold text-forest-950" x-text="$wire.editingId ? 'Edit Pengurus' : 'Tambah Pengurus'"></h3>
                    <p class="mt-1 text-sm text-slate-500">Isi sesuai SK kepengurusan resmi.</p>
                </div>
                <button type="button" wire:click="closeForm" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-forest-50 hover:text-forest-900" aria-label="Tutup form">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save" class="mt-5 grid gap-6 sm:grid-cols-5">
                <div class="space-y-4 sm:col-span-3">
                    <x-ui.input name="name" label="Nama lengkap" wire:model="name"/>
                    <x-ui.input name="position" label="Jabatan" wire:model="position" placeholder="Contoh: Ketua, Sekretaris, Anggota"/>
                    <div>
                        <x-ui.input name="division" label="Bidang (opsional)" wire:model="division" list="division-options"/>
                        <datalist id="division-options">
                            @foreach ($divisionOptions as $option)
                                <option value="{{ $option }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-1.5 text-xs text-slate-400">Kosongkan untuk Pengurus Inti. Tulis tanpa kata “Bidang”.</p>
                    </div>
                    <x-ui.textarea name="description" label="Keterangan (opsional)" rows="2" wire:model="description" hint="Maks. 300 karakter."/>
                    <div class="grid items-end gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.input name="sort_order" type="number" min="0" max="9999" label="Urutan" wire:model="sort_order"/>
                        </div>
                        <x-ui.toggle name="is_active" label="Tampil di website" wire:model="is_active" class="pb-2"/>
                    </div>
                </div>
                <div class="space-y-3 sm:col-span-2">
                    <span class="block text-sm font-medium text-forest-900">Foto</span>
                    <div class="flex items-center gap-3">
                        @if ($newPhoto && $newPhoto->isPreviewable())
                            <img src="{{ $newPhoto->temporaryUrl() }}" alt="Pratinjau foto" class="h-16 w-16 rounded-full border border-forest-100 object-cover">
                        @else
                            <x-ui.avatar :name="$name ?: 'Pengurus'" :src="$photo?->url()" class="h-16 w-16 text-lg"/>
                        @endif
                        @if ($photo || $newPhoto)
                            <button type="button" wire:click="removeMemberPhoto" class="text-xs font-semibold text-red-600 hover:underline">Hapus foto</button>
                        @endif
                    </div>
                    <x-ui.file-upload name="newPhoto" label="Unggah foto" wire:model="newPhoto"/>
                    <div wire:loading wire:target="newPhoto" class="text-xs font-medium text-forest-600">Mengunggah…</div>
                </div>
                <div class="flex justify-end gap-2 sm:col-span-5">
                    <x-ui.button type="button" variant="outline" wire:click="closeForm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save" x-text="$wire.editingId ? 'Simpan Perubahan' : 'Tambah Pengurus'"></span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="$wire.deleteId !== null" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Konfirmasi hapus pengurus">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.cancelDelete()">
            <h3 class="font-display text-lg font-bold text-forest-950">Hapus pengurus ini?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.deleteName"></span>” akan dihapus permanen dari struktur pengurus. Fotonya tetap tersimpan di Media Library.
                Untuk menyembunyikan sementara, gunakan tombol sembunyikan.
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="cancelDelete">Batal</x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="deleteConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteConfirmed">Ya, hapus</span>
                    <span wire:loading wire:target="deleteConfirmed">Menghapus…</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>
