<?php

namespace Tests\Feature\Cms;

use App\Livewire\Cms\Settings\Edit;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_cannot_run_settings_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class)->assertForbidden();
    }

    public function test_admin_saves_settings_and_public_site_reflects_them(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Edit::class)
            ->assertSet('name', 'ICMI Kecamatan Caringin')
            ->set('name', 'ICMI Orsat Uji')
            ->set('short_name', 'ICMI Uji')
            ->set('email', 'sekretariat@contoh.test')
            ->set('phone', '0812-0000-0000')
            ->set('instagram', 'https://instagram.com/akun-uji')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.settings'));

        $this->assertSame('ICMI Orsat Uji', Setting::get('name'));
        $this->assertNull(Setting::get('contact.address'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'settings.updated']);

        auth()->logout();
        $this->get('/kontak')
            ->assertOk()
            ->assertSee('ICMI Orsat Uji')
            ->assertSee('sekretariat@contoh.test')
            ->assertSee('href="https://instagram.com/akun-uji"', false);
        $this->get('/')->assertSee('href="https://instagram.com/akun-uji"', false);
    }

    public function test_unsafe_social_url_and_invalid_contact_are_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Edit::class)
            ->set('facebook', 'javascript:alert(1)')
            ->set('email', 'bukan-email')
            ->set('phone', '<script>')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['facebook', 'email', 'phone', 'name']);

        $this->assertSame(0, Setting::count());
    }

    public function test_logo_upload_replaces_default_and_can_be_reset(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/')->assertSee('images/icmi-emblem.png');

        Livewire::test(Edit::class)
            ->set('newLogo', UploadedFile::fake()->image('logo.png', 400, 400))
            ->call('save')
            ->assertHasNoErrors();

        $media = Media::firstOrFail();
        $this->assertSame((string) $media->id, Setting::get(SiteSettings::LOGO));
        $this->assertSame(1, $media->usageCount());
        $this->get('/')->assertSee($media->url())->assertDontSee('images/icmi-emblem.png');

        Livewire::test(Edit::class)->call('useDefaultLogo')->call('save');

        $this->assertNull(Setting::get(SiteSettings::LOGO));
        $this->assertSame(0, $media->usageCount());
        $this->get('/')->assertSee('images/icmi-emblem.png');
    }
}
