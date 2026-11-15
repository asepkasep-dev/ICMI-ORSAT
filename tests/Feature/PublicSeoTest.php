<?php

namespace Tests\Feature;

use App\Livewire\Cms\Settings\Edit as SettingsEdit;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = User::factory()->create();
    }

    public function test_sitemap_lists_public_pages_and_only_published_content(): void
    {
        $used = Category::factory()->create(['slug' => 'kegiatan']);
        Category::factory()->create(['slug' => 'kosong']);
        $published = Article::factory()->published()->create(['user_id' => $this->author->id, 'slug' => 'berita-terbit']);
        $published->categories()->attach($used);
        Article::factory()->create(['user_id' => $this->author->id, 'slug' => 'berita-draf']);
        Article::factory()->scheduled()->create(['user_id' => $this->author->id, 'slug' => 'berita-terjadwal']);

        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $locs = collect(iterator_to_array($xml->url, false))->map(fn ($u) => (string) $u->loc);

        foreach (['home', 'profile', 'management', 'articles.index', 'gallery', 'contact'] as $route) {
            $this->assertContains(route($route), $locs);
        }
        $this->assertContains(route('articles.show', $published), $locs);
        $this->assertContains(route('categories.show', $used), $locs);
        $this->assertNotContains(url('/kategori/kosong'), $locs);
        $this->assertNotContains(url('/berita/berita-draf'), $locs);
        $this->assertNotContains(url('/berita/berita-terjadwal'), $locs);
        $this->assertStringNotContainsString('/cms', $response->getContent());
    }

    public function test_robots_txt_blocks_cms_and_references_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /cms')
            ->assertSee('Disallow: /login')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_article_detail_has_article_metadata_and_structured_data(): void
    {
        $category = Category::factory()->create(['name' => 'Pendidikan']);
        $article = Article::factory()->published()->create([
            'user_id' => $this->author->id, 'slug' => 'seminar-pendidikan', 'title' => 'Seminar Pendidikan',
            'seo_title' => null, 'seo_description' => null, 'excerpt' => null,
            'content' => '<p>Seminar <strong>pendidikan</strong> &amp; literasi digelar.</p><script>alert(1)</script>',
        ]);
        $article->categories()->attach($category);

        $html = $this->get('/berita/seminar-pendidikan')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('<meta property="article:section" content="Pendidikan">', $html);
        $this->assertStringContainsString('<meta name="description" content="Seminar pendidikan &amp; literasi digelar.', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('articles.show', $article).'">', $html);

        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);
        $data = json_decode($m[1] ?? '', true);
        $this->assertSame('NewsArticle', $data['@type'] ?? null);
        $this->assertSame('Seminar Pendidikan', $data['headline']);
        $this->assertSame($article->published_at->toAtomString(), $data['datePublished']);
        $this->assertSame($this->author->name, $data['author']['name']);
    }

    public function test_seo_fields_override_article_metadata(): void
    {
        Article::factory()->published()->create([
            'user_id' => $this->author->id, 'slug' => 'dengan-seo',
            'seo_title' => 'Judul SEO Khusus', 'seo_description' => 'Deskripsi SEO khusus.',
        ]);

        $this->get('/berita/dengan-seo')
            ->assertSee('<title>Judul SEO Khusus — '.config('icmi.short_name').'</title>', false)
            ->assertSee('<meta name="description" content="Deskripsi SEO khusus.">', false);
    }

    public function test_home_has_organization_structured_data_without_fabricated_contact(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);
        $data = json_decode($m[1] ?? '', true);
        $this->assertSame('Organization', $data['@type'] ?? null);
        $this->assertSame(config('icmi.name'), $data['name']);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('telephone', $data);
        $this->assertArrayNotHasKey('sameAs', $data);
    }

    public function test_search_results_are_noindex_and_paginated_pages_are_self_canonical(): void
    {
        Article::factory()->published()->count(10)->create(['user_id' => $this->author->id]);

        $this->get('/berita?q=rapat')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/berita')->assertDontSee('name="robots"', false);
        $this->get('/berita?page=2')
            ->assertSee('<link rel="canonical" href="'.route('articles.index', ['page' => 2]).'">', false)
            ->assertDontSee('name="robots"', false);
    }

    public function test_google_integrations_render_only_when_configured(): void
    {
        $this->get('/')
            ->assertDontSee('google-site-verification', false)
            ->assertDontSee('googletagmanager.com', false);

        Setting::set('seo.google_site_verification', 'abcDEF_123-xyz');
        Setting::set('seo.google_analytics_id', 'G-ABC123XYZ');

        $this->get('/')
            ->assertSee('<meta name="google-site-verification" content="abcDEF_123-xyz">', false)
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123XYZ', false);

        $this->actingAs(User::factory()->admin()->create())
            ->get('/cms/dashboard')
            ->assertDontSee('googletagmanager.com', false);
    }

    public function test_google_settings_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(SettingsEdit::class)
            ->set('google_site_verification', '<meta name="x" content="y">')
            ->set('google_analytics_id', 'UA-12345-1')
            ->call('save')
            ->assertHasErrors(['google_site_verification', 'google_analytics_id']);

        Livewire::test(SettingsEdit::class)
            ->set('google_site_verification', 'abcDEF_123-xyz')
            ->set('google_analytics_id', 'G-ABC123XYZ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('G-ABC123XYZ', Setting::get('seo.google_analytics_id'));
    }

    public function test_saved_settings_are_visible_immediately_despite_cache(): void
    {
        $this->get('/')->assertSee(config('icmi.name'));

        Setting::set('name', 'ICMI Nama Baru Uji');

        $this->get('/')->assertSee('ICMI Nama Baru Uji');
    }
}
