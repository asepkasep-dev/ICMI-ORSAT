<?php

namespace App\Livewire\Cms\Media;

use App\Models\ActivityLog;
use App\Models\ManagementMember;
use App\Models\Media;
use App\Models\OrganizationProfile;
use App\Support\MediaStorage;
use App\Support\Search;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    #[Url]
    public string $q = '';

    public array $uploads = [];

    public ?int $detailId = null;
    public string $alt = '';
    public string $caption = '';

    public ?int $deleteId = null;
    public string $deleteName = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedUploads(): void
    {
        $this->validate([
            'uploads' => ['array', 'max:'.config('icmi.uploads.max_files')],
            'uploads.*' => MediaStorage::rules(),
        ], [], ['uploads' => 'unggahan', 'uploads.*' => 'gambar']);

        $count = 0;

        foreach ($this->uploads as $upload) {
            $media = MediaStorage::store($upload);
            ActivityLog::record('media.uploaded', $media, 'Mengunggah "'.$media->file_name.'" ke Media Library');
            $count++;
        }

        $this->uploads = [];
        $this->resetPage();

        $suffix = extension_loaded('gd') ? ' dan dioptimasi ke WebP' : '';
        $this->dispatch('toast', message: $count.' gambar berhasil diunggah'.$suffix.'.');
    }

    public function openDetail(int $id): void
    {
        $media = Media::accessibleBy(auth()->user())->find($id);
        abort_unless($media, 404);
        $this->authorize('view', $media);

        $this->detailId = $media->id;
        $this->alt = (string) $media->alt;
        $this->caption = (string) $media->caption;
        $this->resetValidation();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        $this->resetValidation();
    }

    public function saveDetail(): void
    {
        $media = Media::findOrFail($this->detailId);
        $this->authorize('update', $media);

        $data = $this->validate([
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ], [], [
            'alt' => 'teks alternatif',
            'caption' => 'caption',
        ]);

        $media->update($data);
        ActivityLog::record('media.updated', $media, 'Memperbarui metadata "'.$media->file_name.'"');

        $this->detailId = null;
        $this->dispatch('toast', message: 'Metadata "'.$media->file_name.'" diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $media = Media::findOrFail($id);
        $this->authorize('delete', $media);

        if ($media->usageCount() > 0) {
            $this->dispatch('toast', type: 'error', message: 'Media "'.$media->file_name.'" masih digunakan dan tidak dapat dihapus.');

            return;
        }

        $this->deleteId = $media->id;
        $this->deleteName = $media->file_name;
    }

    public function deleteConfirmed(): void
    {
        $media = Media::findOrFail($this->deleteId);
        $this->authorize('delete', $media);

        if ($media->usageCount() > 0) {
            $this->reset(['deleteId', 'deleteName']);
            $this->dispatch('toast', type: 'error', message: 'Media "'.$media->file_name.'" masih digunakan dan tidak dapat dihapus.');

            return;
        }

        MediaStorage::deleteFile($media);
        $name = $media->file_name;
        $media->delete();
        ActivityLog::record('media.deleted', $media, 'Menghapus "'.$name.'" dari Media Library');

        if ($this->detailId === $media->id) {
            $this->detailId = null;
        }

        $this->reset(['deleteId', 'deleteName']);
        $this->dispatch('toast', message: 'Media "'.$name.'" dihapus.');
    }

    public function render()
    {
        $user = auth()->user();
        $query = Media::accessibleBy($user)->with('uploader')->latest();

        if ($this->q !== '') {
            Search::apply($query, ['file_name', 'alt'], $this->q);
        }

        $detail = null;
        $usages = collect();

        if ($this->detailId) {
            $detail = Media::with(['uploader', 'articles' => fn ($q) => $q->withTrashed(), 'featuredIn' => fn ($q) => $q->withTrashed()])->find($this->detailId);

            if ($detail) {
                $usages = $detail->articles->toBase()->map(fn ($a) => ['title' => $a->title, 'type' => 'Foto pendukung'])
                    ->merge($detail->featuredIn->map(fn ($a) => ['title' => $a->title, 'type' => 'Gambar utama']))
                    ->merge(ManagementMember::where('photo_media_id', $detail->id)->get()->map(fn ($m) => ['title' => $m->name, 'type' => 'Foto pengurus']))
                    ->merge(OrganizationProfile::where('chairman_photo_media_id', $detail->id)->exists() ? [['title' => 'Profil Organisasi', 'type' => 'Foto ketua']] : []);
            }
        }

        return view('livewire.cms.media.index', [
            'items' => $query->paginate(18),
            'detail' => $detail,
            'usages' => $usages,
            'total' => Media::accessibleBy($user)->count(),
        ])->layout('components.layouts.cms', ['title' => 'Media Library']);
    }
}