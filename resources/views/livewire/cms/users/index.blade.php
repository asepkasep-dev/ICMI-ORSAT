<div class="space-y-6">
    <x-cms.page-header title="Manajemen Pengguna"
                       description="Buat dan kelola akun Admin dan Writer. Tidak ada registrasi publik — seluruh akun dibuat oleh Admin.">
        <x-ui.button type="button" variant="primary" wire:click="openCreate">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Pengguna
        </x-ui.button>
    </x-cms.page-header>

    <div class="rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-12">
            <div class="relative sm:col-span-8">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari nama atau email…"
                       aria-label="Cari pengguna"
                       class="w-full rounded-xl border border-forest-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
            </div>
            <div class="sm:col-span-4">
                <x-ui.select name="role" wire:model.live="role" aria-label="Filter peran">
                    <option value="">Semua peran</option>
                    @foreach ($roles as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-forest-100 text-left">
                <thead class="bg-forest-50/80">
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-forest-600">
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Peran</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Berita</th>
                        <th class="px-5 py-3.5">Terdaftar</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-forest-50">
                    @forelse ($users as $person)
                        @php
                            $initials = collect(explode(' ', trim($person->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                            $self = $person->id === auth()->id();
                        @endphp
                        <tr class="transition hover:bg-forest-50/50" wire:key="user-{{ $person->id }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $person->isAdmin() ? 'bg-gold-400 text-forest-950' : 'bg-forest-600 text-white' }} font-display text-xs font-bold">{{ $initials }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-forest-950">
                                            {{ $person->name }}
                                            @if ($self) <span class="text-xs font-medium text-slate-400">(Anda)</span> @endif
                                        </p>
                                        <p class="truncate text-xs text-slate-400">{{ $person->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <x-ui.badge :color="$person->isAdmin() ? 'gold' : 'forest'">{{ $person->role->label() }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4">
                                @if ($person->is_active)
                                    <x-ui.badge color="forest">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $person->articles_count }}</td>
                            <td class="px-5 py-4 text-sm text-slate-500">{{ $person->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @unless ($self)
                                        <button type="button" wire:click="toggleActive({{ $person->id }})"
                                                class="rounded-lg p-2 transition {{ $person->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-forest-600 hover:bg-forest-100' }}"
                                                title="{{ $person->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                                aria-label="{{ $person->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $person->name }}">
                                            @if ($person->is_active)
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            @endif
                                        </button>
                                    @endunless
                                    <button type="button" wire:click="openEdit({{ $person->id }})"
                                            class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900" title="Edit" aria-label="Edit {{ $person->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10">
                                <x-ui.empty-state title="Tidak ada pengguna ditemukan"
                                                  description="Ubah kata kunci pencarian atau filter peran.">
                                    <x-ui.button type="button" variant="primary" wire:click="openCreate">Tambah Pengguna</x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $users->links('components.pagination') }}

    <div x-show="$wire.formOpen" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Form pengguna">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.closeForm()">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display text-lg font-bold text-forest-950" x-text="$wire.editingId ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
                    <p class="mt-1 text-sm text-slate-500">Akun digunakan untuk masuk ke CMS.</p>
                </div>
                <button type="button" wire:click="closeForm" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-forest-50 hover:text-forest-900" aria-label="Tutup form">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <x-ui.input name="name" label="Nama lengkap" wire:model="name" placeholder="Contoh: Budi Santoso"/>
                <x-ui.input name="email" type="email" label="Email" wire:model="email" placeholder="nama@icmicaringin.or.id"/>

                @if ($isSelf)
                    <div class="rounded-xl bg-forest-50 p-4 text-xs leading-relaxed text-forest-700">
                        Peran dan status akun Anda sendiri tidak dapat diubah untuk mencegah terkuncinya akses Admin.
                    </div>
                @else
                    <x-ui.select name="roleValue" label="Peran" wire:model="roleValue">
                        @foreach ($roles as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </x-ui.select>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="password" type="password" label="{{ $editingId ? 'Kata sandi baru' : 'Kata sandi' }}" wire:model="password"
                                placeholder="Minimal 8 karakter" autocomplete="new-password"/>
                    <x-ui.input name="password_confirmation" type="password" label="Ulangi kata sandi" wire:model="password_confirmation"
                                placeholder="Ketik ulang" autocomplete="new-password"/>
                </div>
                @if ($editingId)
                    <p class="-mt-2 text-xs text-slate-400">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                @endif

                @unless ($isSelf)
                    <x-ui.toggle name="isActive" label="Akun aktif" wire:model="isActive"
                                 hint="Akun nonaktif tidak dapat masuk ke CMS."/>
                @endunless

                <div class="flex justify-end gap-2 pt-2">
                    <x-ui.button type="button" variant="outline" wire:click="closeForm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save" x-text="$wire.editingId ? 'Simpan Perubahan' : 'Buat Akun'"></span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>