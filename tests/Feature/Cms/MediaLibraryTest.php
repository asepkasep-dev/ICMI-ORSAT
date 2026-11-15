<?php

namespace Tests\Feature\Cms;

use App\Livewire\Cms\Media\Index;
use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function mediaFor(User $user, array $attrs = []): Media
    {
        return Media::create(array_merge([
            'user_id' => $user->id,
            'file_name' => 'foto-'.$user->id.'.jpg',
            'file_path' => 'media/foto-'.$user->id.'.webp',
            'disk' => 'public',
            'mime_type' => 'image/webp',
            'file_size' => 12345,
            'width' => 800,
            'height' => 600,
            'alt' => 'Foto contoh',
        ], $attrs));
    }

    public function test_media_page_requires_authentication(): void
    {
        $this->get('/cms/media')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/cms/media')->assertOk()->assertSee('Media Library');
    }

    public function test_upload_stores_webp_and_records_activity(): void
    {
        Storage::fake('public');
        $writer = User::factory()->create();
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->set('uploads', [UploadedFile::fake()->image('rapat.jpg', 2400, 1600)])
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $media = Media::firstOrFail();
        $this->assertSame($writer->id, $media->user_id);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertLessThanOrEqual(1600, $media->width);
        Storage::disk('public')->assertExists($media->file_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'media.uploaded', 'subject_id' => $media->id]);
    }

    public function test_upload_rejects_non_image_and_oversized_files(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('uploads', [UploadedFile::fake()->create('virus.php', 10, 'application/x-php')])
            ->assertHasErrors(['uploads.0']);

        Livewire::test(Index::class)
            ->set('uploads', [UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3072)])
            ->assertHasErrors(['uploads.0']);

        $this->assertSame(0, Media::count());
    }

    public function test_writer_sees_only_accessible_media(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->mediaFor($writer, ['file_name' => 'milik-sendiri.jpg']);
        $foreign = $this->mediaFor($other, ['file_name' => 'milik-orang-lain.jpg']);
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->assertSee('milik-sendiri.jpg')
            ->assertDontSee('milik-orang-lain.jpg');

        // Detail media asing tidak dapat dibuka.
        Livewire::test(Index::class)->call('openDetail', $foreign->id)->assertStatus(404);

        // Admin melihat semuanya.
        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(Index::class)->assertSee('milik-sendiri.jpg')->assertSee('milik-orang-lain.jpg');
        $this->assertTrue($own->exists);
    }

    public function test_writer_can_view_media_attached_to_own_article(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        $media = $this->mediaFor($other);
        $article = Article::factory()->create(['user_id' => $writer->id]);
        $article->photos()->attach($media->id, ['show_in_gallery' => true, 'sort_order' => 0]);
        $this->actingAs($writer);

        Livewire::test(Index::class)->assertSee($media->file_name);
        Livewire::test(Index::class)->call('openDetail', $media->id)->assertSet('detailId', $media->id);

        // Namun tidak dapat mengubah metadata milik orang lain.
        Livewire::test(Index::class)
            ->call('openDetail', $media->id)
            ->set('alt', 'Diubah paksa')
            ->call('saveDetail')
            ->assertStatus(403);
    }

    public function test_owner_can_update_metadata(): void
    {
        $writer = User::factory()->create();
        $media = $this->mediaFor($writer);
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->call('openDetail', $media->id)
            ->set('alt', 'Suasana rapat pleno')
            ->set('caption', 'Rapat pleno pengurus periode berjalan.')
            ->call('saveDetail')
            ->assertHasNoErrors()
            ->assertSet('detailId', null);

        $this->assertDatabaseHas('media', ['id' => $media->id, 'alt' => 'Suasana rapat pleno']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'media.updated']);
    }

    public function test_alt_is_required(): void
    {
        $writer = User::factory()->create();
        $media = $this->mediaFor($writer);
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->call('openDetail', $media->id)
            ->set('alt', '')
            ->call('saveDetail')
            ->assertHasErrors(['alt']);
    }

    public function test_media_in_use_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $media = $this->mediaFor($admin);
        Storage::disk('public')->put($media->file_path, 'fake-image-bytes');
        $article = Article::factory()->create(['featured_media_id' => $media->id]);
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('confirmDelete', $media->id)
            ->assertSet('deleteId', null)
            ->assertDispatched('toast', type: 'error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->file_path);
        $this->assertTrue($article->exists);
    }

    public function test_unused_media_is_deleted_with_file(): void
    {
        Storage::fake('public');
        $writer = User::factory()->create();
        $media = $this->mediaFor($writer);
        Storage::disk('public')->put($media->file_path, 'fake-image-bytes');
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->call('confirmDelete', $media->id)
            ->assertSet('deleteId', $media->id)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->file_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'media.deleted']);
    }

    public function test_writer_cannot_delete_foreign_media(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        $media = $this->mediaFor($other);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('confirmDelete', $media->id)->assertStatus(403);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_demo_files_are_never_deleted_from_disk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $media = $this->mediaFor($admin, ['file_path' => 'media/demo/contoh.webp']);
        Storage::disk('public')->put($media->file_path, 'demo-bytes');
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('confirmDelete', $media->id)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists('media/demo/contoh.webp');
    }
}