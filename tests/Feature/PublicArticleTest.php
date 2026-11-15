<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Livewire\Public\ArticleIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicArticleTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = User::factory()->create();
    }

    public function test_only_published_articles_are_listed_publicly(): void
    {
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Terbit Publik', 'slug' => 'berita-terbit-publik',
        ]);
        Article::factory()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Draf Rahasia', 'slug' => 'berita-draf-rahasia',
        ]);
        Article::factory()->scheduled()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Terjadwal Nanti', 'slug' => 'berita-terjadwal-nanti',
        ]);
        Article::factory()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Menunggu Review', 'slug' => 'berita-menunggu-review',
            'status' => ArticleStatus::InReview,
        ]);

        $this->get('/berita')
            ->assertOk()
            ->assertSee('Berita Terbit Publik')
            ->assertDontSee('Berita Draf Rahasia')
            ->assertDontSee('Berita Terjadwal Nanti')
            ->assertDontSee('Berita Menunggu Review');
    }

    public function test_published_article_detail_is_accessible(): void
    {
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Judul Detail Artikel', 'slug' => 'judul-detail-artikel',
        ]);

        $this->get('/berita/judul-detail-artikel')
            ->assertOk()
            ->assertSee('Judul Detail Artikel');
    }

    public function test_non_published_article_details_return_404(): void
    {
        Article::factory()->create(['user_id' => $this->author->id, 'slug' => 'draf-slug']);
        Article::factory()->scheduled()->create(['user_id' => $this->author->id, 'slug' => 'terjadwal-slug']);
        Article::factory()->create([
            'user_id' => $this->author->id, 'slug' => 'masa-depan',
            'status' => ArticleStatus::Published, 'published_at' => now()->addDay(),
        ]);

        $this->get('/berita/draf-slug')->assertNotFound();
        $this->get('/berita/terjadwal-slug')->assertNotFound();
        $this->get('/berita/masa-depan')->assertNotFound();
    }

    public function test_search_filters_articles(): void
    {
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Kegiatan Ramadan Bersama', 'slug' => 'kegiatan-ramadan',
        ]);
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Rapat Pleno Pengurus', 'slug' => 'rapat-pleno',
        ]);

        Livewire::test(ArticleIndex::class)
            ->set('q', 'Ramadan')
            ->assertSee('Kegiatan Ramadan Bersama')
            ->assertDontSee('Rapat Pleno Pengurus');
    }

    public function test_search_treats_wildcard_characters_literally(): void
    {
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Diskon 50% Iuran Anggota', 'slug' => 'diskon-iuran',
        ]);
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Rapat Pleno Pengurus', 'slug' => 'rapat-pleno',
            'excerpt' => 'Ringkasan rapat', 'content' => '<p>Isi rapat pleno.</p>',
        ]);

        Livewire::test(ArticleIndex::class)
            ->set('q', '50%')
            ->assertSee('Diskon 50% Iuran Anggota')
            ->assertDontSee('Rapat Pleno Pengurus')
            ->set('q', '_')
            ->assertDontSee('Diskon 50% Iuran Anggota')
            ->assertDontSee('Rapat Pleno Pengurus');
    }

    public function test_category_page_limits_results(): void
    {
        $category = Category::factory()->create(['name' => 'Dakwah', 'slug' => 'dakwah']);
        $inCategory = Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Dakwah Utama', 'slug' => 'berita-dakwah',
        ]);
        $inCategory->categories()->attach($category);
        $draft = Article::factory()->create([
            'user_id' => $this->author->id, 'title' => 'Draf Dakwah Rahasia', 'slug' => 'draf-dakwah',
        ]);
        $draft->categories()->attach($category);
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Berita Umum Lain', 'slug' => 'berita-umum',
        ]);

        $this->get('/kategori/dakwah')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Dakwah')
            ->assertSee('Berita Dakwah Utama')
            ->assertDontSee('Draf Dakwah Rahasia')
            ->assertDontSee('Berita Umum Lain')
            ->assertSee('<link rel="canonical" href="'.route('categories.show', $category).'">', false);

        Livewire::test(ArticleIndex::class, ['category' => $category])
            ->set('q', 'Utama')
            ->assertSee('Berita Dakwah Utama')
            ->assertDontSee('Berita Umum Lain');
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->get('/kategori/tidak-ada')->assertNotFound();
    }

    public function test_category_links_point_to_category_pages(): void
    {
        $category = Category::factory()->create(['name' => 'Pendidikan', 'slug' => 'pendidikan']);
        $article = Article::factory()->published()->create(['user_id' => $this->author->id, 'slug' => 'berita-pendidikan']);
        $article->categories()->attach($category);

        $this->get('/berita')->assertSee('href="'.route('categories.show', $category).'"', false);
        $this->get('/berita/berita-pendidikan')->assertSee('href="'.route('categories.show', $category).'"', false);
    }

    public function test_sort_orders_articles(): void
    {
        $older = Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'A Artikel Lama', 'slug' => 'artikel-lama',
            'published_at' => now()->subDays(10),
        ]);
        $newer = Article::factory()->published()->create([
            'user_id' => $this->author->id, 'title' => 'Z Artikel Baru', 'slug' => 'artikel-baru',
            'published_at' => now()->subDay(),
        ]);

        $oldest = Livewire::test(ArticleIndex::class)->set('sort', 'oldest')->viewData('articles');
        $this->assertSame($older->id, $oldest->first()->id);

        $newest = Livewire::test(ArticleIndex::class)->set('sort', 'newest')->viewData('articles');
        $this->assertSame($newer->id, $newest->first()->id);
    }

    public function test_featured_article_is_not_duplicated_in_grid(): void
    {
        $featured = Article::factory()->published()->featured()->create([
            'user_id' => $this->author->id, 'title' => 'Sorotan Utama', 'slug' => 'sorotan-utama',
        ]);

        $component = Livewire::test(ArticleIndex::class);

        $this->assertSame($featured->id, $component->viewData('featured')?->id);
        $this->assertFalse($component->viewData('articles')->pluck('id')->contains($featured->id));
    }

    public function test_articles_are_paginated(): void
    {
        Article::factory()->published()->count(12)->create(['user_id' => $this->author->id]);

        $component = Livewire::test(ArticleIndex::class);
        $this->assertCount(9, $component->viewData('articles'));
        $this->assertSame(12, $component->viewData('articles')->total());

        $component->call('nextPage');
        $this->assertCount(3, $component->viewData('articles'));
    }

    public function test_reset_filters_clears_state(): void
    {
        Livewire::test(ArticleIndex::class)
            ->set('q', 'ramadan')
            ->set('sort', 'title')
            ->call('resetFilters')
            ->assertSet('q', '')
            ->assertSet('sort', 'newest');
    }

    public function test_reset_filters_on_category_page_returns_to_all_articles(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ArticleIndex::class, ['category' => $category])
            ->call('resetFilters')
            ->assertRedirect(route('articles.index'));
    }

    public function test_category_cannot_be_changed_from_client(): void
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::test(ArticleIndex::class)->set('categoryId', 1);
    }
}