<div class="space-y-6">
    <x-cms.page-header title="Profil Organisasi"
                       description="Sejarah, visi, misi, tujuan, dan sambutan Ketua yang tampil di halaman Profil. Bagian yang dikosongkan akan ditampilkan sebagai “sedang disiapkan”.">
        <x-ui.button href="{{ route('profile') }}" target="_blank" variant="outline">Lihat halaman publik</x-ui.button>
    </x-cms.page-header>

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-12">
        <div class="min-w-0 space-y-6 lg:col-span-8">
            <div class="space-y-3 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Sejarah</h2>
                <div wire:ignore x-data="trixEditor('history')" class="trix-wrap rounded-xl border border-forest-200 shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                    <input id="profile-history-input" type="hidden" value="{{ $history }}">
                    <trix-editor input="profile-history-input" placeholder="Tulis sejarah organisasi…" aria-label="Sejarah organisasi"></trix-editor>
                </div>
                @error('history') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Visi, Misi, dan Tujuan</h2>
                <x-ui.textarea name="vision" label="Visi" rows="3" wire:model="vision"/>
                <x-ui.textarea name="mission" label="Misi" rows="6" wire:model="mission"
                               hint="Satu butir misi per baris; penomoran ditambahkan otomatis."/>
                <x-ui.textarea name="goals" label="Tujuan" rows="6" wire:model="goals"
                               hint="Satu butir tujuan per baris."/>
            </div>

            <div class="space-y-3 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Sambutan Ketua</h2>
                <div wire:ignore x-data="trixEditor('chairman_message')" class="trix-wrap rounded-xl border border-forest-200 shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                    <input id="profile-chairman-message-input" type="hidden" value="{{ $chairman_message }}">
                    <trix-editor input="profile-chairman-message-input" placeholder="Tulis naskah sambutan Ketua…" aria-label="Sambutan Ketua"></trix-editor>
                </div>
                @error('chairman_message') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-6 lg:col-span-4">
            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                <h2 class="font-display text-base font-bold text-forest-950">Ketua</h2>
                <x-ui.input name="chairman_name" label="Nama Ketua" wire:model="chairman_name"/>

                <div class="flex items-center gap-4">
                    @if ($newPhoto && $newPhoto->isPreviewable())
                        <img src="{{ $newPhoto->temporaryUrl() }}" alt="Pratinjau foto Ketua" class="h-20 w-20 rounded-full border border-forest-100 object-cover">
                    @else
                        <x-ui.avatar :name="$chairman_name ?: 'Ketua'" :src="$photo?->url()" class="h-20 w-20 text-2xl"/>
                    @endif
                    <div class="min-w-0 text-xs text-slate-500">
                        @if ($newPhoto)
                            <p class="font-semibold text-forest-800">Foto baru — simpan untuk menerapkan</p>
                        @elseif ($photo)
                            <p class="truncate font-semibold text-forest-800">{{ $photo->file_name }}</p>
                        @else
                            <p>Belum ada foto; inisial nama ditampilkan.</p>
                        @endif
                        @if ($photo || $newPhoto)
                            <button type="button" wire:click="removeChairmanPhoto" class="mt-1 font-semibold text-red-600 hover:underline">Hapus foto</button>
                        @endif
                    </div>
                </div>
                <x-ui.file-upload name="newPhoto" label="Unggah foto Ketua" wire:model="newPhoto"/>
                <div wire:loading wire:target="newPhoto" class="text-xs font-medium text-forest-600">Mengunggah…</div>
                <p class="text-xs text-slate-400">Foto yang dihapus tetap tersimpan di Media Library.</p>

                <x-ui.button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Simpan Profil</span>
                    <span wire:loading wire:target="save">Menyimpan…</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
