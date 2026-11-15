<form wire:submit="save" class="space-y-6">
    <x-cms.page-header :title="$articleId ? 'Edit Berita' : 'Tulis Berita'"
                       :description="$articleId ? 'Perbarui konten berita. Perubahan status publikasi dikelola pada alur workflow.' : 'Berita baru disimpan sebagai draf. Alur review dan publikasi menyusul pada Milestone 3.'">
        <x-ui.button :href="route('cms.articles')" variant="outline">Batal</x-ui.button>
    </x-cms.page-header>

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-8">
            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <x-ui.input name="title" label="Judul berita" wire:model.live="title"
                            placeholder="Contoh: Rapat Pleno Pengurus Tetapkan Program Kerja"/>
                <div>
                    <label for="slug" class="mb-1.5 block text-sm font-medium text-forest-900">Slug URL</label>
                    <div class="flex items-center overflow-hidden rounded-xl border border-forest-200 bg-white shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                        <span class="border-r border-forest-100 bg-forest-50 px-3 py-2.5 text-sm text-slate-400">/berita/</span>
                        <input type="text" name="slug" id="slug" wire:model.live="slug"
                               class="w-full border-0 bg-transparent px-3 py-2.5 text-sm text-ink focus:outline-none focus:ring-0"
                               placeholder="slug-otomatis-dari-judul">
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Otomatis dari judul; dapat disesuaikan. Harus unik.</p>
                    @error('slug')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.textarea name="excerpt" label="Ringkasan" rows="3" wire:model="excerpt"
                               hint="Maks. 500 karakter. Tampil pada kartu berita dan meta description."
                               placeholder="Ringkasan singkat berita…"/>
                <x-ui.textarea name="content" label="Isi berita" rows="14" wire:model="content"
                               hint="Pisahkan paragraf dengan baris kosong. Editor rich text (TipTap) hadir pada Milestone 3."
                               placeholder="Tulis isi berita di sini…"/>
            </div>

            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-forest-950">SEO</h3>
                <x-ui.input name="seo_title" label="SEO title (opsional)" wire:model="seo_title"
                            placeholder="Judul untuk mesin pencari"/>
                <x-ui.textarea name="seo_description" label="SEO description (opsional)" rows="2" wire:model="seo_description"
                               hint="Maks. 300 karakter." placeholder="Deskripsi untuk mesin pencari…"/>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-4">
            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-display text-base font-bold text-forest-950">Publikasi</h3>
                    @if ($article)
                        <x-cms.status-badge :status="$article->status"/>
                    @else
                        <x-cms.status-badge status="draft"/>
                    @endif
                </div>
                <p class="text-xs leading-relaxed text-slate-400">
                    Tombol terbit, review, dan penjadwalan hadir pada Milestone 3 sesuai PRD v2.1.
                </p>
                @if (auth()->user()->isAdmin())
                    <x-ui.toggle name="is_featured" label="Berita unggulan" wire:model="is_featured"
                                 hint="Tampil sebagai sorotan di beranda dan daftar berita."/>
                @endif
            </div>

            <div class="rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-forest-950">Kategori</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($categoryOptions as $cat)
                        <x-ui.checkbox name="categories[]" :label="$cat->name" value="{{ $cat->id }}" wire:model="categories"/>
                    @endforeach
                </div>
                @error('categories')
                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-forest-950">Gambar Utama</h3>
                @if ($featuredMedia && ! $removeFeatured)
                    <div class="relative overflow-hidden rounded-xl border border-forest-100">
                        <img src="{{ $featuredMedia->url() }}" alt="{{ $featuredMedia->alt }}" class="aspect-video w-full object-cover">
                        <button type="button" wire:click="removeFeatured"
                                class="absolute right-2 top-2 rounded-lg bg-forest-950/70 p-1.5 text-white backdrop-blur transition hover:bg-red-600"
                                aria-label="Hapus gambar utama">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @elseif ($newFeatured && $newFeatured->isPreviewable())
                    <img src="{{ $newFeatured->temporaryUrl() }}" alt="Pratinjau gambar utama" class="aspect-video w-full rounded-xl border border-forest-100 object-cover">
                @endif
                <x-ui.file-upload name="newFeatured" label="Unggah gambar utama" wire:model="newFeatured"/>
                <div wire:loading wire:target="newFeatured" class="text-xs font-medium text-forest-600">Mengunggah…</div>
            </div>

            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-forest-950">Foto Pendukung</h3>
                @if ($article && $article->photos->isNotEmpty())
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($article->photos as $photo)
                            <img src="{{ $photo->url() }}" alt="{{ $photo->alt }}" class="aspect-square w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                @endif
                <x-ui.file-upload name="newPhotos" label="Unggah foto pendukung" :multiple="true" wire:model="newPhotos"
                                  hint="Foto otomatis masuk galeri publik setelah berita terbit."/>
                <div wire:loading wire:target="newPhotos" class="text-xs font-medium text-forest-600">Mengunggah…</div>
                @error('newPhotos.*')
                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="sticky bottom-4 z-30 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-forest-100 bg-white/95 p-4 shadow-lg shadow-forest-900/10 backdrop-blur">
        <p class="text-xs text-slate-400">
            @if ($articleId) Perubahan tersimpan pada berita yang ada. @else Berita baru disimpan sebagai draf. @endif
        </p>
        <div class="flex items-center gap-2">
            <x-ui.button :href="route('cms.articles')" variant="ghost">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
                <span wire:loading.remove wire:target="save">{{ $articleId ? 'Simpan Perubahan' : 'Simpan Draf' }}</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </x-ui.button>
        </div>
    </div>
</form>