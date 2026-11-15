<?php

namespace App\Livewire\Cms\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\MediaStorage;
use App\Support\SiteSettings;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    private const MAP = [
        'name' => 'name',
        'short_name' => 'short_name',
        'long_name' => 'long_name',
        'tagline' => 'tagline',
        'term' => 'term',
        'description' => 'description',
        'address' => 'contact.address',
        'email' => 'contact.email',
        'phone' => 'contact.phone',
        'instagram' => 'social.instagram',
        'facebook' => 'social.facebook',
        'youtube' => 'social.youtube',
        'tiktok' => 'social.tiktok',
    ];

    public string $name = '';
    public string $short_name = '';
    public string $long_name = '';
    public string $tagline = '';
    public string $term = '';
    public string $description = '';
    public string $address = '';
    public string $email = '';
    public string $phone = '';
    public string $instagram = '';
    public string $facebook = '';
    public string $youtube = '';
    public string $tiktok = '';

    public $newLogo = null;
    public bool $resetLogo = false;

    public function mount(): void
    {
        $this->authorize('update', Setting::class);

        $values = SiteSettings::values();
        foreach (self::MAP as $property => $key) {
            $this->{$property} = (string) $values[$key];
        }
    }

    public function updatedNewLogo(): void
    {
        $this->resetLogo = false;
    }

    public function useDefaultLogo(): void
    {
        $this->newLogo = null;
        $this->resetLogo = true;
    }

    protected function rules(): array
    {
        $social = ['nullable', 'string', 'max:255', 'url:http,https'];

        return [
            'name' => ['required', 'string', 'max:100'],
            'short_name' => ['required', 'string', 'max:40'],
            'long_name' => ['required', 'string', 'max:150'],
            'tagline' => ['required', 'string', 'max:150'],
            'term' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:300'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s.]+$/'],
            'instagram' => $social,
            'facebook' => $social,
            'youtube' => $social,
            'tiktok' => $social,
            'newLogo' => ['nullable', ...MediaStorage::rules()],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama organisasi',
            'short_name' => 'nama singkat',
            'long_name' => 'nama lengkap',
            'tagline' => 'tagline',
            'term' => 'masa bakti',
            'description' => 'deskripsi website',
            'address' => 'alamat sekretariat',
            'email' => 'email organisasi',
            'phone' => 'nomor kontak',
            'newLogo' => 'logo',
        ];
    }

    public function save()
    {
        $this->authorize('update', Setting::class);
        $this->validate();

        $before = Setting::pluck('value', 'key')->all();

        foreach (self::MAP as $property => $key) {
            $value = trim($this->{$property});
            Setting::set($key, $value === '' ? null : $value);
        }

        if ($this->newLogo) {
            Setting::set(SiteSettings::LOGO, (string) MediaStorage::store($this->newLogo)->id);
        } elseif ($this->resetLogo) {
            Setting::set(SiteSettings::LOGO, null);
        }

        if (Setting::pluck('value', 'key')->all() != $before) {
            ActivityLog::record('settings.updated', null, 'Memperbarui pengaturan website');
        }

        session()->flash('status', 'Pengaturan website tersimpan.');

        return $this->redirectRoute('cms.settings');
    }

    public function render()
    {
        return view('livewire.cms.settings.edit', [
            'logo' => $this->resetLogo ? null : SiteSettings::logo(),
        ])->layout('components.layouts.cms', ['title' => 'Pengaturan Website']);
    }
}
