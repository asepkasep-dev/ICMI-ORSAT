<?php

namespace Tests\Feature\Cms;

use App\Enums\ArticleStatus;
use App\Livewire\Cms\Articles\Form;
use App\Livewire\Cms\Articles\Index;
use App\Livewire\Cms\Dashboard;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $longContent = 'Isi berita yang cukup panjang untuk memenuhi aturan validasi minimal dua puluh karakter.';

    public function test_create_form_is_accessible_to_authenticated_users(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            $this->actingAs($user)->get('/cms/berita/buat')->assertOk()->assertSee('Tulis Berita');
        }
    }

    public function test_writer_sees_only_own_articles_in_index(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        Article::factory()->create(['user_id' => $writer->id, 'title' => 'Berita Milik Saya', 'slug' => 'berita-milik-saya']);
        Article::factory()->create(['user_id' => $other->id, 'title' => 'Berita Penulis Lain', 'slug' => 'berita-penulis-lain']);

        $this->actingAs($writer);
        Livewire::test(Index::class)
            ->assertSee('Berita Milik Saya')
            ->assertDontSee('Berita Penulis Lain');
    }

    public function test_admin_sees_all_articles_in_index(): void
    {
        $admin = User::factory()->admin()->create();
        Article::factory()->create(['title' => 'Berita Satu', 'slug' => 'berita-satu']);
        Article::factory()->create(['title' => 'Berita Dua', 'slug' => 'berita-dua']);

        $this->actingAs($admin);
        Livewire::test(Index::class)->assertSee('Berita Satu')->assertSee('Berita Dua');
    }

    public function test_writer_can_create_draft_article(): void
    {
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);

        Livewire::test(Form::class)
            ->set('title', 'Berita Baru Karya Writer')
            ->set('content', $this->longContent)
            ->set('categories', [(string) $category->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.articles'));

        $this->assertDatabaseHas('articles', [
            'title' => 'Berita Baru Karya Writer',
            'user_id' => $writer->id,
            'status' => ArticleStatus::Draft->value,
        ]);
    }

    public function test_create_validation_requires_title_content_categories(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Form::class)
            ->set('title', 'Abc')
            ->set('content', 'pendek')
            ->call('save')
            ->assertHasErrors(['title', 'content', 'categories']);
    }

    public function test_writer_can_edit_own_article(): void
    {
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $article = Article::factory()->create(['user_id' => $writer->id, 'title' => 'Judul Asli', 'slug' => 'judul-asli']);
        $article->categories()->attach($category);
        $this->actingAs($writer);

        Livewire::test(Form::class, ['article' => $article])
            ->set('title', 'Judul Diperbarui')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.articles'));

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Judul Diperbarui']);
    }

    public function test_saving_without_changes_is_not_logged(): void
    {
        $writer = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $writer->id]);
        $article->categories()->attach(Category::factory()->create());
        $this->actingAs($writer);

        // Simpan pertama menormalkan konten hasil factory lewat sanitizer.
        Livewire::test(Form::class, ['article' => $article])->call('save');
        ActivityLog::query()->delete();

        Livewire::test(Form::class, ['article' => $article->fresh()])->call('save')->assertHasNoErrors();
        $this->assertDatabaseMissing('activity_logs', ['action' => 'article.updated']);

        Livewire::test(Form::class, ['article' => $article->fresh()])
            ->set('categories', [(string) Category::factory()->create()->id])
            ->call('save');
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.updated', 'subject_id' => $article->id]);
    }

    public function test_failed_save_rolls_back_and_removes_uploaded_files(): void
    {
        Storage::fake('public');
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);
        ActivityLog::creating(fn () => throw new \RuntimeException('Gagal simulasi'));

        try {
            Livewire::test(Form::class)
                ->set('title', 'Berita Yang Gagal Disimpan')
                ->set('content', $this->longContent)
                ->set('categories', [(string) $category->id])
                ->set('newFeatured', UploadedFile::fake()->image('utama.jpg', 200, 200))
                ->set('newPhotos', [UploadedFile::fake()->image('pendukung.jpg', 200, 200)])
                ->call('save');
            $this->fail('Simpan seharusnya gagal.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Gagal simulasi', $e->getMessage());
        }

        $this->assertDatabaseMissing('articles', ['title' => 'Berita Yang Gagal Disimpan']);
        $this->assertSame(0, Media::count());
        $this->assertSame([], Storage::disk('public')->allFiles('media'));
    }

    public function test_writer_cannot_edit_foreign_article(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $other->id, 'slug' => 'berita-orang-lain']);

        $this->actingAs($writer);
        $this->assertFalse($writer->can('update', $article));
        $this->get(route('cms.articles.edit', $article))->assertForbidden();
    }

    public function test_admin_can_edit_foreign_article(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $other->id, 'slug' => 'berita-admin-edit']);

        $this->actingAs($admin);
        $this->assertTrue($admin->can('update', $article));
        $this->get(route('cms.articles.edit', $article))->assertOk();
    }

    public function test_writer_cannot_delete_article(): void
    {
        $writer = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $writer->id]);
        $this->actingAs($writer);

        $this->assertFalse($writer->can('delete', $article));
        Livewire::test(Index::class)->call('confirmDelete', $article->id)->assertStatus(403);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'deleted_at' => null]);
    }

    public function test_admin_can_delete_article(): void
    {
        $admin = User::factory()->admin()->create();
        $article = Article::factory()->create(['title' => 'Berita Dihapus Admin']);
        $this->actingAs($admin);

        $this->assertTrue($admin->can('delete', $article));
        Livewire::test(Index::class)
            ->call('confirmDelete', $article->id)
            ->assertSet('confirmId', $article->id)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

    public function test_featured_flag_is_forced_false_for_writer(): void
    {
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);

        Livewire::test(Form::class)
            ->set('title', 'Berita Writer Biasa')
            ->set('content', $this->longContent)
            ->set('categories', [(string) $category->id])
            ->set('is_featured', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', ['title' => 'Berita Writer Biasa', 'is_featured' => false]);
    }

    public function test_writer_edit_preserves_featured_flag_set_by_admin(): void
    {
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $article = Article::factory()->create([
            'user_id' => $writer->id,
            'title' => 'Berita Unggulan Writer',
            'slug' => 'berita-unggulan-writer',
            'is_featured' => true,
        ]);
        $article->categories()->attach($category);
        $this->actingAs($writer);

        Livewire::test(Form::class, ['article' => $article])
            ->set('title', 'Judul Baru Tetap Unggulan')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'is_featured' => true]);
    }

    public function test_featured_media_id_cannot_be_set_from_client(): void
    {
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        try {
            Livewire::test(Form::class)
                ->set('title', 'Berita Dengan Media Invalid')
                ->set('content', $this->longContent)
                ->set('categories', [(string) $category->id])
                ->set('featuredMediaId', 999999)
                ->call('save');
        } finally {
            $this->assertDatabaseMissing('articles', ['title' => 'Berita Dengan Media Invalid']);
        }
    }

    public function test_upload_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);

        Livewire::test(Form::class)
            ->set('title', 'Berita Dengan Upload')
            ->set('content', $this->longContent)
            ->set('categories', [(string) $category->id])
            ->set('newFeatured', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['newFeatured']);
    }

    public function test_valid_image_upload_is_stored_and_linked(): void
    {
        Storage::fake('public');
        $writer = User::factory()->create();
        $category = Category::factory()->create();
        $this->actingAs($writer);

        Livewire::test(Form::class)
            ->set('title', 'Berita Dengan Gambar')
            ->set('content', $this->longContent)
            ->set('categories', [(string) $category->id])
            ->set('newFeatured', UploadedFile::fake()->image('cover.jpg', 800, 600))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.articles'));

        $article = Article::where('title', 'Berita Dengan Gambar')->firstOrFail();
        $this->assertNotNull($article->featured_media_id);
        $this->assertDatabaseHas('media', ['id' => $article->featured_media_id]);
        Storage::disk('public')->assertExists($article->featuredMedia->file_path);
    }

    public function test_admin_dashboard_statistics_come_from_database(): void
    {
        $admin = User::factory()->admin()->create();
        Article::factory()->published()->count(3)->create();
        Article::factory()->count(2)->create();
        Article::factory()->scheduled()->create();
        $this->actingAs($admin);

        $stats = Livewire::test(Dashboard::class)->viewData('articleStats');

        $this->assertSame(6, $stats['total']);
        $this->assertSame(3, $stats['published']);
        $this->assertSame(2, $stats['draft']);
        $this->assertSame(1, $stats['scheduled']);
    }

    public function test_writer_dashboard_counts_only_own_articles(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();
        Article::factory()->published()->count(2)->create(['user_id' => $writer->id]);
        Article::factory()->create(['user_id' => $writer->id]);
        Article::factory()->published()->count(5)->create(['user_id' => $other->id]);
        $this->actingAs($writer);

        $stats = Livewire::test(Dashboard::class)->viewData('articleStats');

        $this->assertSame(3, $stats['total']);
        $this->assertSame(2, $stats['published']);
        $this->assertSame(1, $stats['draft']);
    }
}