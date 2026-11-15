<?php

namespace App\Livewire\Cms\Management;

use App\Models\ActivityLog;
use App\Models\ManagementMember;
use App\Models\Media;
use App\Support\MediaStorage;
use App\Support\Search;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    private const FORM = ['editingId', 'name', 'position', 'division', 'description', 'sort_order', 'is_active', 'photoId', 'removePhoto', 'newPhoto'];

    #[Url]
    public string $q = '';

    public bool $formOpen = false;
    #[Locked]
    public ?int $editingId = null;
    public string $name = '';
    public string $position = '';
    public string $division = '';
    public string $description = '';
    public $sort_order = '';
    public bool $is_active = true;
    #[Locked]
    public ?int $photoId = null;
    public bool $removePhoto = false;
    public $newPhoto = null;

    #[Locked]
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function mount(): void
    {
        $this->authorize('viewAny', ManagementMember::class);
    }

    public function openCreate(): void
    {
        $this->authorize('create', ManagementMember::class);

        $this->reset(self::FORM);
        $this->sort_order = (string) ((int) ManagementMember::max('sort_order') + 1);
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function openEdit(int $id): void
    {
        $member = ManagementMember::findOrFail($id);
        $this->authorize('update', $member);

        $this->reset(self::FORM);
        $this->editingId = $member->id;
        $this->name = $member->name;
        $this->position = $member->position;
        $this->division = (string) $member->division;
        $this->description = (string) $member->description;
        $this->sort_order = (string) $member->sort_order;
        $this->is_active = $member->is_active;
        $this->photoId = $member->photo_media_id;
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->reset(self::FORM);
        $this->resetValidation();
    }

    public function updatedNewPhoto(): void
    {
        $this->removePhoto = false;
    }

    public function removeMemberPhoto(): void
    {
        $this->newPhoto = null;
        $this->removePhoto = true;
    }

    public function save(): void
    {
        $member = $this->editingId ? ManagementMember::findOrFail($this->editingId) : new ManagementMember();
        $this->authorize($member->exists ? 'update' : 'create', $member->exists ? $member : ManagementMember::class);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'position' => ['required', 'string', 'min:2', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'newPhoto' => ['nullable', ...MediaStorage::rules()],
        ], [], [
            'name' => 'nama',
            'position' => 'jabatan',
            'division' => 'bidang',
            'description' => 'keterangan',
            'sort_order' => 'urutan',
            'newPhoto' => 'foto',
        ]);

        $stored = null;

        try {
            DB::transaction(function () use ($member, &$stored) {
                $photoId = $this->removePhoto ? null : $this->photoId;
                if ($this->newPhoto) {
                    $stored = MediaStorage::store($this->newPhoto);
                    $photoId = $stored->id;
                }

                $member->fill([
                    'name' => trim($this->name),
                    'position' => trim($this->position),
                    'division' => trim($this->division) ?: null,
                    'description' => trim($this->description) ?: null,
                    'sort_order' => (int) $this->sort_order,
                    'is_active' => $this->is_active,
                    'photo_media_id' => $photoId,
                ]);

                if ($member->isDirty()) {
                    $isNew = ! $member->exists;
                    $member->save();
                    ActivityLog::record(
                        $isNew ? 'member.created' : 'member.updated',
                        $member,
                        ($isNew ? 'Menambahkan' : 'Memperbarui').' pengurus "'.$member->name.'"'
                    );
                }
            });
        } catch (\Throwable $e) {
            if ($stored) {
                MediaStorage::deleteFile($stored);
            }

            throw $e;
        }

        $this->dispatch('toast', message: 'Data pengurus "'.$member->name.'" tersimpan.');
        $this->closeForm();
    }

    public function toggleActive(int $id): void
    {
        $member = ManagementMember::findOrFail($id);
        $this->authorize('update', $member);

        $member->update(['is_active' => ! $member->is_active]);
        ActivityLog::record(
            $member->is_active ? 'member.activated' : 'member.deactivated',
            $member,
            ($member->is_active ? 'Menampilkan' : 'Menyembunyikan').' pengurus "'.$member->name.'"'
        );
        $this->dispatch('toast', message: $member->is_active
            ? '"'.$member->name.'" kini tampil di website.'
            : '"'.$member->name.'" disembunyikan dari website.');
    }

    public function move(int $id, string $direction): void
    {
        $member = ManagementMember::findOrFail($id);
        $this->authorize('update', $member);

        DB::transaction(function () use ($member, $direction) {
            $ordered = ManagementMember::orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $peers = $ordered->filter(fn ($m) => $m->division === $member->division)->values();
            $index = $peers->search(fn ($m) => $m->id === $member->id);
            $neighbour = $peers->get($direction === 'up' ? $index - 1 : $index + 1);

            if (! $neighbour) {
                return;
            }

            $ids = $ordered->pluck('id')->all();
            $a = array_search($member->id, $ids, true);
            $b = array_search($neighbour->id, $ids, true);
            [$ids[$a], $ids[$b]] = [$ids[$b], $ids[$a]];

            $current = $ordered->pluck('sort_order', 'id');
            foreach ($ids as $i => $memberId) {
                if ((int) $current[$memberId] !== $i + 1) {
                    ManagementMember::whereKey($memberId)->update(['sort_order' => $i + 1]);
                }
            }

            ActivityLog::record('member.reordered', $member, 'Mengubah urutan pengurus "'.$member->name.'"');
        });
    }

    public function confirmDelete(int $id): void
    {
        $member = ManagementMember::findOrFail($id);
        $this->authorize('delete', $member);

        $this->deleteId = $member->id;
        $this->deleteName = $member->name;
    }

    public function cancelDelete(): void
    {
        $this->reset(['deleteId', 'deleteName']);
    }

    public function deleteConfirmed(): void
    {
        $member = ManagementMember::findOrFail($this->deleteId);
        $this->authorize('delete', $member);

        $member->delete();
        ActivityLog::record('member.deleted', $member, 'Menghapus pengurus "'.$member->name.'"');
        $this->dispatch('toast', message: 'Pengurus "'.$member->name.'" dihapus.');
        $this->reset(['deleteId', 'deleteName']);
    }

    public function render()
    {
        $query = ManagementMember::with('photo')->orderBy('sort_order')->orderBy('id');

        if ($this->q !== '') {
            Search::apply($query, ['name', 'position', 'division'], $this->q);
        }

        $members = $query->get();
        $groups = $members->groupBy(fn ($m) => $m->division ?? '');
        if ($groups->has('')) {
            $groups = collect(['' => $groups->pull('')])->union($groups);
        }

        return view('livewire.cms.management.index', [
            'groups' => $groups,
            'total' => ManagementMember::count(),
            'activeCount' => ManagementMember::where('is_active', true)->count(),
            'divisionOptions' => ManagementMember::whereNotNull('division')->distinct()->orderBy('division')->pluck('division'),
            'photo' => ! $this->removePhoto && $this->photoId ? Media::find($this->photoId) : null,
        ])->layout('components.layouts.cms', ['title' => 'Struktur Pengurus']);
    }
}
