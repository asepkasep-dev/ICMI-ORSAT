<div class="space-y-6">
    <x-cms.page-header title="Manajemen Berita"
                       :description="auth()->user()->isAdmin() ? 'Kelola seluruh berita: tulis, review, jadwalkan, terbitkan, dan arsipkan.' : 'Kelola berita milik Anda: tulis, ajukan review, dan terbitkan.'">
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
                        @php
                            $isAdmin = auth()->user()->isAdmin();
                            $st = $article->status;
                            $showMenu = $isAdmin || in_array($st, [\App\Enums\ArticleStatus::Draft, \App\Enums\ArticleStatus::InReview, \App\Enums\ArticleStatus::Revision], true);
                        @endphp
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
                                        @if ($st === \App\Enums\ArticleStatus::Revision && $article->revision_note)
                                            <p class="mt-0.5 truncate text-xs font-medium text-red-600" title="{{ $article->revision_note }}">Revisi: {{ $article->revision_note }}</p>
                                        @endif
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

                                    @if ($showMenu)
                                        <div x-data="{ open: false, top: 0, left: 0 }" class="inline-block">
                                            <button type="button" x-ref="trigger"
                                                    x-on:click.stop="const r = $refs.trigger.getBoundingClientRect(); top = r.bottom + 6; left = Math.max(8, r.right - 224); open = !open"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-forest-100 hover:text-forest-900"
                                                    title="Aksi publikasi" aria-label="Aksi publikasi untuk {{ $article->title }}" aria-haspopup="menu">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/></svg>
                                            </button>
                                            <template x-teleport="body">
                                                <div x-show="open" x-cloak
                                                     x-on:click.outside="open = false"
                                                     x-on:scroll.window="open = false"
                                                     x-on:resize.window="open = false"
                                                     x-on:keydown.escape.window="open = false"
                                                     class="fixed z-[80] w-56 overflow-hidden rounded-xl border border-forest-100 bg-white py-1.5 shadow-xl shadow-forest-900/15"
                                                     :style="`top: ${top}px; left: ${left}px`"
                                                     role="menu" aria-label="Aksi publikasi berita">
                                                    @if ($st !== \App\Enums\ArticleStatus::Published && auth()->user()->can('publish', $article))
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="publish({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-forest-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                                            Terbitkan Sekarang
                                                        </button>
                                                    @endif
                                                    @if (in_array($st, [\App\Enums\ArticleStatus::Draft, \App\Enums\ArticleStatus::Revision], true))
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="submitReview({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                                            Ajukan untuk Review
                                                        </button>
                                                    @endif
                                                    @can('schedule', $article)
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="openSchedule({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                            {{ $st === \App\Enums\ArticleStatus::Scheduled ? 'Ubah Jadwal' : 'Jadwalkan' }}…
                                                        </button>
                                                    @endcan
                                                    @can('review', $article)
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="openRevision({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                                            Minta Revisi…
                                                        </button>
                                                    @endcan
                                                    @if ($isAdmin && in_array($st, [\App\Enums\ArticleStatus::Published, \App\Enums\ArticleStatus::Scheduled], true))
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="archive({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                                            Arsipkan
                                                        </button>
                                                    @endif
                                                    @if ($isAdmin && $st === \App\Enums\ArticleStatus::Archived)
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="restore({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-forest-800 transition hover:bg-forest-50">
                                                            <svg class="h-4 w-4 text-forest-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                                            Pulihkan ke Draf
                                                        </button>
                                                    @endif
                                                    @if ($isAdmin)
                                                        <div class="my-1 border-t border-forest-50"></div>
                                                        <button type="button" role="menuitem" x-on:click="open = false" wire:click="confirmDelete({{ $article->id }})"
                                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                            Hapus
                                                        </button>
                                                    @endif
                                                </div>
                                            </template>
                                        </div>
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

    {{ $articles->links('components.pagination') }}

    <div x-show="$wire.confirmId !== null" x-cloak
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

    <div x-show="$wire.scheduleId !== null" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Jadwalkan publikasi berita">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.set('scheduleId', null)">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-forest-950">Jadwalkan publikasi</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.scheduleTitle"></span>” akan otomatis tayang pada waktu yang dipilih.
            </p>
            <div class="mt-4">
                <label for="scheduleAt" class="mb-1.5 block text-sm font-medium text-forest-900">Waktu publikasi</label>
                <input type="datetime-local" id="scheduleAt" wire:model="scheduleAt"
                       class="block w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 text-sm text-ink shadow-sm focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25">
                @error('scheduleAt') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="$set('scheduleId', null)">Batal</x-ui.button>
                <x-ui.button type="button" variant="primary" wire:click="scheduleConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="scheduleConfirmed">Simpan Jadwal</span>
                    <span wire:loading wire:target="scheduleConfirmed">Menjadwalkan…</span>
                </x-ui.button>
            </div>
        </div>
    </div>

    <div x-show="$wire.revisionId !== null" x-cloak
         class="fixed inset-0 z-[90] flex items-center justify-center bg-forest-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-label="Minta revisi berita">
        <div class="w-full max-w-md rounded-2xl border border-forest-100 bg-white p-7 shadow-2xl"
             x-on:click.outside="$wire.set('revisionId', null)">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </span>
            <h3 class="mt-4 font-display text-lg font-bold text-forest-950">Kembalikan untuk revisi</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                “<span x-text="$wire.revisionTitle"></span>” akan dikembalikan ke penulis dengan catatan perbaikan.
            </p>
            <div class="mt-4">
                <label for="revisionNoteModal" class="mb-1.5 block text-sm font-medium text-forest-900">Catatan revisi</label>
                <textarea id="revisionNoteModal" wire:model="revisionNote" rows="4"
                          placeholder="Jelaskan bagian yang perlu diperbaiki…"
                          class="block w-full rounded-xl border border-forest-200 bg-white px-4 py-2.5 text-sm text-ink shadow-sm focus:border-forest-500 focus:outline-none focus:ring-2 focus:ring-forest-500/25"></textarea>
                @error('revisionNote') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" wire:click="$set('revisionId', null)">Batal</x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="revisionConfirmed" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="revisionConfirmed">Kembalikan</span>
                    <span wire:loading wire:target="revisionConfirmed">Mengembalikan…</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</div>