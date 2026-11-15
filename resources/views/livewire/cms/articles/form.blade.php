<div class="space-y-6">
    <x-cms.page-header :title="$articleId ? 'Edit Berita' : 'Tulis Berita'"
                       description="Lengkapi konten, pilih kategori dan gambar, lalu simpan sebagai draf atau terbitkan.">
        <x-ui.button :href="route('cms.articles')" variant="outline">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Kembali
        </x-ui.button>
    </x-cms.page-header>

    @if ($article && $article->status === \App\Enums\ArticleStatus::Revision && $article->revision_note)
        <x-ui.alert type="error">
            <span class="font-semibold">Catatan revisi dari Admin:</span> {{ $article->revision_note }}
        </x-ui.alert>
    @endif

    @if ($article && $article->status === \App\Enums\ArticleStatus::Archived)
        <x-ui.alert type="warning">
            Berita ini diarsipkan dan tidak tampil di website. Admin dapat memulihkannya ke draf dari halaman Manajemen Berita.
        </x-ui.alert>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-12">
        <div class="min-w-0 space-y-6 lg:col-span-8">
            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <x-ui.input name="title" label="Judul berita" wire:model.blur="title"
                            placeholder="Contoh: Rapat Pleno Pengurus Tetapkan Program Kerja"/>

                <div>
                    <label for="slug" class="mb-1.5 block text-sm font-medium text-forest-900">Slug URL</label>
                    <div class="flex items-center overflow-hidden rounded-xl border border-forest-200 bg-white shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                        <span class="border-r border-forest-100 bg-forest-50 px-3 py-2.5 text-sm text-slate-400">/berita/</span>
                        <input type="text" name="slug" id="slug" wire:model.blur="slug"
                               class="w-full border-0 bg-transparent px-3 py-2.5 text-sm text-ink focus:outline-none focus:ring-0"
                               placeholder="slug-otomatis-dari-judul">
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Otomatis dari judul; dapat disesuaikan. Harus unik.</p>
                    @error('slug')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="mb-1.5 block text-sm font-medium text-forest-900">Isi berita</span>
                    <div wire:ignore x-data="trixEditor" class="trix-wrap rounded-xl border border-forest-200 shadow-sm transition focus-within:border-forest-500 focus-within:ring-2 focus-within:ring-forest-500/25">
                        <input id="article-content-input" type="hidden" value="{{ $content }}">
                        <trix-editor input="article-content-input" placeholder="Tulis isi berita lengkap di sini…" aria-label="Isi berita"></trix-editor>
                    </div>
                    @error('content')
                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-red-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400">Format teks, heading, kutipan, dan tautan tersedia di toolbar. Gambar dikelola lewat panel gambar di samping.</p>
                </div>

                <x-ui.textarea name="excerpt" label="Ringkasan" rows="3" wire:model.blur="excerpt"
                               hint="Opsional — tampil di kartu berita dan hasil pencarian. Maksimal 500 karakter."
                               placeholder="Ringkasan singkat berita…"/>
            </div>

            <div class="space-y-5 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="h-4.5 w-4.5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <h3 class="font-display text-base font-bold text-forest-950">SEO</h3>
                </div>
                <x-ui.input name="seo_title" label="SEO title (opsional)" wire:model.blur="seo_title"
                            placeholder="Judul untuk mesin pencari"/>
                <x-ui.textarea name="seo_description" label="SEO description (opsional)" rows="2" wire:model.blur="seo_description"
                               hint="Maksimal 300 karakter." placeholder="Deskripsi untuk mesin pencari…"/>
            </div>
        </div>

        <div class="min-w-0 space-y-6 lg:col-span-4">
            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm"
                 x-data="{ showSchedule: false, showRevision: false }">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-display text-base font-bold text-forest-950">Publikasi</h3>
                    <x-cms.status-badge :status="$article?->status ?? 'draft'"/>
                </div>

                @if ($article?->published_at)
                    <p class="text-xs leading-relaxed text-slate-500">
                        @if ($article->status === \App\Enums\ArticleStatus::Scheduled)
                            Dijadwalkan terbit: <span class="font-semibold text-sky-700">{{ $article->published_at->translatedFormat('d M Y H:i') }}</span>
                        @elseif ($article->isPublished())
                            Tayang sejak: <span class="font-semibold text-forest-700">{{ $article->published_at->translatedFormat('d M Y H:i') }}</span>
                        @endif
                    </p>
                @endif

                @php
                    $status = $article?->status ?? \App\Enums\ArticleStatus::Draft;
                    $isAdmin = auth()->user()->isAdmin();
                    $subject = $article ?? new \App\Models\Article(['user_id' => auth()->id(), 'status' => $status]);
                    $canPublish = auth()->user()->can('publish', $subject);
                    $canSchedule = auth()->user()->can('schedule', $subject);
                    $canReview = $articleId && auth()->user()->can('review', $subject);
                @endphp

                @if ($status !== \App\Enums\ArticleStatus::Archived)
                    <div class="grid gap-2">
                        @if ($canPublish)
                        <x-ui.button type="button" variant="primary" wire:click="publish" wire:loading.attr="disabled" wire:target="publish">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                            <span wire:loading.remove wire:target="publish">{{ $status === \App\Enums\ArticleStatus::Published ? 'Perbarui Publikasi' : 'Terbitkan Sekarang' }}</span>
                            <span wire:loading wire:target="publish">Menerbitkan…</span>
                        </x-ui.button>
                        @endif

                        @if (in_array($status, [\App\Enums\ArticleStatus::Draft, \App\Enums\ArticleStatus::Revision], true))
                            <x-ui.button type="button" variant="outline" wire:click="submitReview" wire:loading.attr="disabled" wire:target="submitReview">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                <span wire:loading.remove wire:target="submitReview">Ajukan untuk Review</span>
                                <span wire:loading wire:target="submitReview">Mengajukan…</span>
                            </x-ui.button>
                        @endif

                        @if ($canSchedule)
                            <x-ui.button type="button" variant="outline" x-on:click="showSchedule = !showSchedule; showRevision = false">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Jadwalkan
                            </x-ui.button>
                            <div x-show="showSchedule" x-cloak class="space-y-2 rounded-xl border border-sky-200 bg-sky-50/60 p-3">
                                <label for="scheduledAt" class="block text-xs font-semibold text-sky-900">Waktu publikasi</label>
                                <input type="datetime-local" id="scheduledAt" wire:model="scheduledAt"
                                       class="block w-full rounded-lg border border-sky-200 bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/25">
                                @error('scheduledAt') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                                <x-ui.button type="button" variant="primary" class="w-full px-3! py-2! text-xs!" wire:click="schedule" wire:loading.attr="disabled" wire:target="schedule">
                                    <span wire:loading.remove wire:target="schedule">Simpan Jadwal</span>
                                    <span wire:loading wire:target="schedule">Menjadwalkan…</span>
                                </x-ui.button>
                            </div>
                        @endif

                        @if ($canReview)
                                <x-ui.button type="button" variant="outline" x-on:click="showRevision = !showRevision; showSchedule = false">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    Minta Revisi
                                </x-ui.button>
                                <div x-show="showRevision" x-cloak class="space-y-2 rounded-xl border border-red-200 bg-red-50/60 p-3">
                                    <label for="revisionNote" class="block text-xs font-semibold text-red-900">Catatan revisi untuk penulis</label>
                                    <textarea id="revisionNote" wire:model="revisionNote" rows="3"
                                              placeholder="Jelaskan bagian yang perlu diperbaiki…"
                                              class="block w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/25"></textarea>
                                    @error('revisionNote') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                                    <x-ui.button type="button" variant="danger" class="w-full px-3! py-2! text-xs!" wire:click="requestRevision" wire:loading.attr="disabled" wire:target="requestRevision">
                                        <span wire:loading.remove wire:target="requestRevision">Kembalikan untuk Revisi</span>
                                        <span wire:loading wire:target="requestRevision">Mengembalikan…</span>
                                    </x-ui.button>
                                </div>
                        @endif
                    </div>
                @else
                    <p class="rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
                        Berita diarsipkan tidak dapat diterbitkan langsung. Pulihkan ke draf terlebih dahulu melalui menu aksi di Manajemen Berita.
                    </p>
                @endif

                @if ($isAdmin)
                    <div class="border-t border-forest-100 pt-4">
                        <x-ui.toggle name="is_featured" label="Berita unggulan" wire:model="is_featured"
                                     hint="Tampil sebagai sorotan di beranda dan daftar berita."/>
                    </div>
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
                @error('categories.*')
                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="font-display text-base font-bold text-forest-950">Gambar Utama</h3>
                    <button type="button" wire:click="openMediaPicker"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-forest-700 transition hover:bg-forest-50 hover:text-forest-900">
                        Pilih dari Media
                    </button>
                </div>
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
                <x-ui.file-upload name="newFeatured" label="Unggah gambar baru" wire:model="newFeatured"/>
                <div wire:loading wire:target="newFeatured" class="text-xs font-medium text-forest-600">Mengunggah…</div>
                @error('featuredMediaId') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
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

        <div class="sticky bottom-4 z-30 lg:col-span-12">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-forest-100 bg-white/95 p-4 shadow-lg shadow-forest-900/10 backdrop-blur">
                <p class="text-xs text-slate-400">
                    @if ($articleId) Perubahan tersimpan pada berita yang ada. @else Berita baru disimpan sebagai draf. @endif
                </p>
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit" variant="outline" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Simpan {{ $articleId ? 'Perubahan' : 'Draf' }}</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                    @if ($canPublish)
                        <x-ui.button type="button" variant="gold" wire:click="publish" wire:loading.attr="disabled" wire:target="publish">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                            <span wire:loading.remove wire:target="publish">Terbitkan</span>
                            <span wire:loading wire:target="publish">Menerbitkan…</span>
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div x-show="$wire.mediaPickerOpen" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Pilih gambar utama dari media library">
        <div class="flex max-h-[85vh] w-full max-w-3xl flex-col rounded-2xl border border-forest-100 bg-white shadow-2xl"
             x-on:click.outside="$wire.closeMediaPicker()">
            <div class="flex items-center justify-between gap-3 border-b border-forest-100 p-5">
                <div>
                    <h3 class="font-display text-lg font-bold text-forest-950">Pilih Gambar Utama</h3>
                    <p class="text-xs text-slate-400">Media yang Anda unggah atau yang terkait dengan berita Anda.</p>
                </div>
                <button type="button" wire:click="closeMediaPicker" class="rounded-lg p-2 text-slate-400 transition hover:bg-forest-50 hover:text-forest-900" aria-label="Tutup pemilih media">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="border-b border-forest-100 p-4">
                <input type="search" wire:model.live.debounce.300ms="mediaSearch" placeholder="Cari nama file atau alt…"
                       aria-label="Cari media"
                       class="w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
            </div>
            <div class="cms-scrollbar grid flex-1 grid-cols-3 gap-3 overflow-y-auto p-4 sm:grid-cols-4">
                @forelse ($mediaOptions as $m)
                    <button type="button" wire:click="selectMedia({{ $m->id }})" wire:key="pick-{{ $m->id }}"
                            class="group relative aspect-square overflow-hidden rounded-xl border-2 border-transparent bg-forest-50 transition hover:border-forest-600 focus-visible:border-forest-600 focus-visible:outline-none">
                        <img src="{{ $m->url() }}" alt="{{ $m->alt }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <span class="absolute inset-x-0 bottom-0 truncate bg-forest-950/70 px-2 py-1 text-left text-[10px] font-medium text-white opacity-0 transition group-hover:opacity-100">{{ $m->file_name }}</span>
                    </button>
                @empty
                    <div class="col-span-full py-10 text-center">
                        <p class="text-sm font-semibold text-forest-900">Belum ada media yang cocok</p>
                        <p class="mt-1 text-xs text-slate-400">Unggah gambar baru lewat panel Gambar Utama, atau kelola di Media Library.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>