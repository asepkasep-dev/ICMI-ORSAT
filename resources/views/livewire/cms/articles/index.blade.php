<div class="space-y-6">
    <x-cms.page-header title="Manajemen Berita"
                       :description="auth()->user()->isAdmin() ? 'Kelola seluruh berita: tulis, edit, periksa status, dan hapus.' : 'Kelola berita milik Anda: tulis, edit, dan pantau statusnya.'">
        <x-ui.button :href="route('cms.articles.create')" variant="primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tulis Berita
        </x-ui.button>
    </x-cms.page-header>

    <div class="rounded-2xl border border-forest-100 bg-white p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-12">
            <div class="relative lg:col-span-6">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="Cari judul atau slug…"
                       aria-label="Cari berita"
                       class="w-full rounded-xl border border-forest-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
            </div>
            <div class="lg:col-span-3">
                <x-ui.select name="status" wire:model.live="status" aria-label="Filter status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="lg:col-span-3">
                <x-ui.select name="category" wire:model.live="category" aria-label="Filter kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
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
                        <th class="px-5 py-3.5">Berita</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Penulis</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-forest-50">
                    @forelse ($articles as $article)
                        <tr class="transition hover:bg-forest-50/50" wire:key="article-{{ $article->id }}">
                            <td class="max-w-sm px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($article->featuredMedia)
                                        <img src="{{ $article->featuredMedia->url() }}" alt="" class="h-11 w-16 shrink-0 rounded-lg object-cover">
                                    @else
                                        <span class="flex h-11 w-16 shrink-0 items-center justify-center rounded-lg bg-forest-100 text-forest-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-forest-950">{{ $article->title }}</p>
                                        <p class="truncate text-xs text-slate-400">/{{ $article->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($article->categories->take(2) as $cat)
                                        <x-ui.badge color="gold" class="px-2! py-0.5! text-[10px]!">{{ $cat->name }}</x-ui.badge>
                                    @empty
                                        <span class="text-xs text-slate-400">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $article->author?->name ?? '—' }}</td>
                            <td class="px-5 py-4"><x-cms.status-badge :status="$article->status"/></td>
                            <td class="px-5 py-4 text-sm text-slate-500">
                                {{ ($article->published_at ?? $article->updated_at)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('cms.articles.edit', $article) }}"
                                       class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900" title="Edit" aria-label="Edit {{ $article->title }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                    </a>
                                    @if ($article->isPublished())
                                        <a href="{{ route('articles.show', $article) }}" target="_blank" rel="noopener"
                                           class="rounded-lg p-2 text-forest-600 transition hover:bg-forest-100 hover:text-forest-900" title="Lihat di website" aria-label="Lihat {{ $article->title }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        </a>
                                    @endif
                                    @if (auth()->user()->isAdmin())
                                        <button type="button" wire:click="confirmDelete({{ $article->id }})"
                                                class="rounded-lg p-2 text-red-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus" aria-label="Hapus {{ $article->title }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10">
                                <x-ui.empty-state title="Belum ada berita"
                                                  description="Mulai menulis berita pertama Anda, atau ubah filter pencarian.">
                                    <x-ui.button :href="route('cms.articles.create')" variant="primary">Tulis Berita</x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $articles->links() }}

    <div x-show="$wire.confirmId !== null" x-cloak x-transition.opacity
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Konfirmasi hapus berita">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.set('confirmId', null)">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-forest-950">Hapus berita ini?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.confirmTitle"></span>” akan dihapus dari daftar berita.
                Tindakan ini dapat dipulihkan melalui database (soft delete).
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="$set('confirmId', null)">Batal</x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="deleteConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteConfirmed">Ya, hapus</span>
                    <span wire:loading wire:target="deleteConfirmed">Menghapus…</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>