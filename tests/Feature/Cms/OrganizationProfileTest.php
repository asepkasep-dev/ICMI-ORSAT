<?php

namespace Tests\Feature\Cms;

use App\Livewire\Cms\Profile\Edit;
use App\Models\Media;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_cannot_run_profile_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class)->assertForbidden();
    }

    public function test_admin_saves_profile_with_sanitized_html(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Edit::class)
            ->set('history', '<div>Berdiri tahun uji.<script>alert(1)</script></div><h1>Judul</h1>')
            ->set('vision', '  Visi uji  ')
            ->set('mission', "Misi satu\n\n  Misi dua  \r\n")
            ->set('goals', "Tujuan satu\nTujuan dua")
            ->set('chairman_name', 'Nama Uji')
            ->set('chairman_message', '<div><a href="javascript:alert(1)">tautan</a> Sambutan uji</div>')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.profile'));

        $profile = OrganizationProfile::firstOrFail();
        $this->assertStringNotContainsString('<script', $profile->history);
        $this->assertStringNotContainsString('<h1', $profile->history);
        $this->assertStringContainsString('Berdiri tahun uji.', $profile->history);
        $this->assertStringNotContainsString('javascript:', $profile->chairman_message);
        $this->assertSame('Visi uji', $profile->vision);
        $this->assertSame(['Misi satu', 'Misi dua'], $profile->missionItems());
        $this->assertDatabaseHas('activity_logs', ['action' => 'profile.updated']);

        auth()->logout();
        $this->get('/profil-organisasi')->assertOk()
            ->assertSee('Berdiri tahun uji.')
            ->assertSee('Misi dua')
            ->assertSee('Tujuan dua')
            ->assertSee('Nama Uji')
            ->assertSee('Sambutan uji');
    }

    public function test_empty_editor_content_is_stored_as_null(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        OrganizationProfile::create(['history' => 'Lama', 'vision' => 'Lama']);

        Livewire::test(Edit::class)
            ->assertSet('history', '<p>Lama</p>')
            ->set('history', '<div><br></div>')
            ->set('vision', '   ')
            ->call('save');

        $profile = OrganizationProfile::firstOrFail();
        $this->assertNull($profile->history);
        $this->assertNull($profile->vision);
        $this->assertSame(1, OrganizationProfile::count());
    }

    public function test_chairman_photo_upload_and_removal(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Edit::class)
            ->set('newPhoto', UploadedFile::fake()->image('ketua.jpg', 600, 600))
            ->call('save')
            ->assertHasNoErrors();

        $media = Media::firstOrFail();
        $this->assertSame($media->id, OrganizationProfile::firstOrFail()->chairman_photo_media_id);

        Livewire::test(Edit::class)->call('removeChairmanPhoto')->call('save');

        $this->assertNull(OrganizationProfile::firstOrFail()->chairman_photo_media_id);
        $this->assertTrue(Media::whereKey($media->id)->exists());
    }
}
