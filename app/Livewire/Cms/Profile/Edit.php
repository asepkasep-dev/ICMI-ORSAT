<?php

namespace App\Livewire\Cms\Profile;

use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\OrganizationProfile;
use App\Support\ContentSanitizer;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public string $history = '';
    public string $vision = '';
    public string $mission = '';
    public string $goals = '';
    public string $chairman_name = '';
    public string $chairman_message = '';

    #[Locked]
    public ?int $chairmanPhotoId = null;
    public bool $removePhoto = false;
    public $newPhoto = null;

    public function mount(): void
    {
        $this->authorize('update', OrganizationProfile::class);

        $profile = OrganizationProfile::instance();
        $this->history = ContentSanitizer::clean($profile->history);
        $this->vision = (string) $profile->vision;
        $this->mission = implode("\n", $profile->missionItems());
        $this->goals = implode("\n", $profile->goalItems());
        $this->chairman_name = (string) $profile->chairman_name;
        $this->chairman_message = ContentSanitizer::clean($profile->chairman_message);
        $this->chairmanPhotoId = $profile->chairman_photo_media_id;
    }

    public function updatedNewPhoto(): void
    {
        $this->removePhoto = false;
    }

    public function removeChairmanPhoto(): void
    {
        $this->newPhoto = null;
        $this->removePhoto = true;
    }

    protected function rules(): array
    {
        return [
            'history' => ['nullable', 'string', 'max:50000'],
            'vision' => ['nullable', 'string', 'max:1000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'goals' => ['nullable', 'string', 'max:5000'],
            'chairman_name' => ['nullable', 'string', 'max:100'],
            'chairman_message' => ['nullable', 'string', 'max:20000'],
            'newPhoto' => ['nullable', ...MediaStorage::rules()],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'history' => 'sejarah',
            'vision' => 'visi',
            'mission' => 'misi',
            'goals' => 'tujuan',
            'chairman_name' => 'nama ketua',
            'chairman_message' => 'sambutan ketua',
            'newPhoto' => 'foto ketua',
        ];
    }

    public function save()
    {
        $this->authorize('update', OrganizationProfile::class);
        $this->validate();

        $profile = OrganizationProfile::instance();
        $stored = null;

        try {
            DB::transaction(function () use ($profile, &$stored) {
                $photoId = $this->removePhoto ? null : $this->chairmanPhotoId;
                if ($this->newPhoto) {
                    $stored = MediaStorage::store($this->newPhoto);
                    $photoId = $stored->id;
                }

                $profile->fill([
                    'history' => $this->html($this->history),
                    'vision' => $this->plain($this->vision),
                    'mission' => $this->lines($this->mission),
                    'goals' => $this->lines($this->goals),
                    'chairman_name' => $this->plain($this->chairman_name),
                    'chairman_message' => $this->html($this->chairman_message),
                    'chairman_photo_media_id' => $photoId,
                ]);

                if ($profile->isDirty()) {
                    $profile->save();
                    ActivityLog::record('profile.updated', $profile, 'Memperbarui profil organisasi');
                }
            });
        } catch (\Throwable $e) {
            if ($stored) {
                MediaStorage::deleteFile($stored);
            }

            throw $e;
        }

        session()->flash('status', 'Profil organisasi tersimpan.');

        return $this->redirectRoute('cms.profile');
    }

    private function html(string $value): ?string
    {
        return ContentSanitizer::text($value) === '' ? null : ContentSanitizer::clean($value);
    }

    private function plain(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function lines(string $value): ?string
    {
        $items = array_filter(array_map('trim', preg_split('/\R/', $value)));

        return $items === [] ? null : implode("\n", $items);
    }

    public function render()
    {
        return view('livewire.cms.profile.edit', [
            'photo' => ! $this->removePhoto && $this->chairmanPhotoId ? Media::find($this->chairmanPhotoId) : null,
        ])->layout('components.layouts.cms', ['title' => 'Profil Organisasi']);
    }
}
