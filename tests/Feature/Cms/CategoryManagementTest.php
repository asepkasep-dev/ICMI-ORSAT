<?php

namespace Tests\Feature\Cms;

use App\Livewire\Cms\Categories\Index;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_is_admin_only(): void
    {
        $this->get('/cms/kategori')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/cms/kategori')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/cms/kategori')->assertOk()->assertSee('Kategori Berita');
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('name', 'Kegiatan Organisasi')
            ->assertSet('slug', 'kegiatan-organisasi')
            ->set('description', 'Berita seputar kegiatan organisasi.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false);

        $this->assertDatabaseHas('categories', ['name' => 'Kegiatan Organisasi', 'slug' => 'kegiatan-organisasi']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'category.created']);
    }

    public function test_category_name_and_slug_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Opini', 'slug' => 'opini']);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('name', 'Opini')
            ->call('save')
            ->assertHasErrors(['name', 'slug']);
    }

    public function test_admin_can_edit_category(): void
    {
        $category = Category::factory()->create(['name' => 'Nama Lama', 'slug' => 'nama-lama']);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openEdit', $category->id)
            ->assertSet('name', 'Nama Lama')
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Nama Baru']);
        // Slug tidak berubah otomatis saat edit karena sudah dianggap disentuh.
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'nama-lama']);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('deleteCount', 0)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'category.deleted']);
    }

    public function test_category_in_use_requires_move_target(): void
    {
        $source = Category::factory()->create(['name' => 'Sumber']);
        $target = Category::factory()->create(['name' => 'Tujuan']);
        $article = Article::factory()->create();
        $article->categories()->attach($source);
        $this->actingAs(User::factory()->admin()->create());

        // Tanpa tujuan: gagal dan kategori tetap ada.
        Livewire::test(Index::class)
            ->call('confirmDelete', $source->id)
            ->assertSet('deleteCount', 1)
            ->call('deleteConfirmed')
            ->assertHasErrors(['moveTo']);

        $this->assertDatabaseHas('categories', ['id' => $source->id]);

        // Dengan tujuan: berita dipindah, kategori dihapus.
        Livewire::test(Index::class)
            ->call('confirmDelete', $source->id)
            ->set('moveTo', (string) $target->id)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categories', ['id' => $source->id]);
        $this->assertSame(0, DB::table('article_category')->where('category_id', $source->id)->count());
        $this->assertSame(1, DB::table('article_category')->where('category_id', $target->id)->where('article_id', $article->id)->count());
    }

    public function test_move_target_cannot_be_the_deleted_category_itself(): void
    {
        $source = Category::factory()->create();
        $article = Article::factory()->create();
        $article->categories()->attach($source);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('confirmDelete', $source->id)
            ->set('moveTo', (string) $source->id)
            ->call('deleteConfirmed')
            ->assertHasErrors(['moveTo']);

        $this->assertDatabaseHas('categories', ['id' => $source->id]);
    }

    public function test_article_with_both_categories_is_not_duplicated_on_move(): void
    {
        $source = Category::factory()->create();
        $target = Category::factory()->create();
        $article = Article::factory()->create();
        $article->categories()->attach([$source->id, $target->id]);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('confirmDelete', $source->id)
            ->set('moveTo', (string) $target->id)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertSame(1, DB::table('article_category')->where('article_id', $article->id)->count());
    }

    public function test_writer_cannot_manage_categories_via_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertStatus(403);
    }
}