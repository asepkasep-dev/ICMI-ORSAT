<div class="space-y-6">
    <x-cms.page-header title="Pengaturan Website"
                       description="Identitas, logo, deskripsi SEO, kontak, dan media sosial yang tampil di seluruh website publik. Kontak dan media sosial yang dikosongkan akan ditampilkan sebagai “belum tersedia”."/>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="font-display text-base font-bold text-forest-950">Identitas Organisasi</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" label="Nama organisasi" wire:model="name" placeholder="ICMI Kecamatan Caringin"/>
                    <div>
                        <x-ui.input name="short_name" label="Nama singkat" wire:model="short_name" placeholder="ICMI Caringin"/>
                        <p class="mt-1.5 text-xs text-slate-400">Tampil di header dan sidebar; kata kedua dan seterusnya berwarna emas.</p>
                    </div>
                </div>
                <x-ui.input name="long_name" label="Nama lengkap" wire:model="long_name" placeholder="Ikatan Cendekiawan Muslim se-Indonesia — Kecamatan Caringin"/>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="tagline" label="Tagline" wire:model="tagline"/>
                    <x-ui.input name="term" label="Masa bakti" wire:model="term" placeholder="2026–2031"/>
                </div>
                <x-ui.textarea name="description" label="Deskripsi website (SEO)" rows="3" wire:model="description"
                               hint="Dipakai sebagai meta description dan deskripsi saat dibagikan. Idealnya 120–160 karakter, maksimal 300."/>
            </div>

            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Logo</h2>
                <div class="flex items-center gap-4 rounded-xl border border-forest-100 bg-forest-950 p-5">
                    @if ($newLogo && $newLogo->isPreviewable())
                        <img src="{{ $newLogo->temporaryUrl() }}" alt="Pratinjau logo baru" class="h-16 w-16 object-contain">
                    @else
                        <img src="{{ $logo?->url() ?? asset('images/icmi-emblem.png') }}" alt="Logo saat ini" class="h-16 w-16 object-contain">
                    @endif
                    <div class="min-w-0 text-xs leading-relaxed text-forest-200">
                        @if ($newLogo)
                            <p class="font-semibold text-gold-300">Logo baru — simpan untuk menerapkan</p>
                        @elseif ($logo)
                            <p class="font-semibold text-white">Logo unggahan</p>
                            <p class="truncate text-forest-300">{{ $logo->file_name }}</p>
                        @else
                            <p class="font-semibold text-white">Logo ICMI nasional</p>
                            <p class="text-forest-300">Bawaan, sesuai logo resmi icmi.id</p>
                        @endif
                    </div>
                </div>
                @if ($logo || $newLogo)
                    <button type="button" wire:click="useDefaultLogo"
                            class="text-xs font-semibold text-forest-700 underline-offset-2 transition hover:text-forest-950 hover:underline">
                        Kembalikan ke logo ICMI nasional
                    </button>
                @endif
                <x-ui.file-upload name="newLogo" label="Ganti logo" wire:model="newLogo"
                                  hint="Disarankan gambar persegi PNG/WebP berlatar transparan. {{ \App\Support\MediaStorage::hint() }}"/>
                <div wire:loading wire:target="newLogo" class="text-xs font-medium text-forest-600">Mengunggah…</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Kontak Resmi</h2>
                <x-ui.textarea name="address" label="Alamat sekretariat" rows="2" wire:model="address"/>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="email" type="email" label="Email organisasi" wire:model="email" placeholder="sekretariat@contoh.or.id"/>
                    <x-ui.input name="phone" label="Nomor kontak" wire:model="phone" placeholder="0812-3456-7890"/>
                </div>
            </div>

            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-base font-bold text-forest-950">Media Sosial</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="instagram" type="url" label="Instagram" wire:model="instagram" placeholder="https://instagram.com/…"/>
                    <x-ui.input name="facebook" type="url" label="Facebook" wire:model="facebook" placeholder="https://facebook.com/…"/>
                    <x-ui.input name="youtube" type="url" label="YouTube" wire:model="youtube" placeholder="https://youtube.com/@…"/>
                    <x-ui.input name="tiktok" type="url" label="TikTok" wire:model="tiktok" placeholder="https://tiktok.com/@…"/>
                </div>
                <p class="text-xs text-slate-400">Gunakan tautan lengkap yang diawali https://.</p>
            </div>
        </div>

        <div class="flex justify-end">
            <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </x-ui.button>
        </div>
    </form>
</div>
