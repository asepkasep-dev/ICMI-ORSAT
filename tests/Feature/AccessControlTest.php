<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_cms_to_login(): void
    {
        $this->get('/cms/dashboard')->assertRedirect(route('login'));
        $this->get('/cms/pengguna')->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/cms/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang')
            ->assertSee('Manajemen Pengguna');
    }

    public function test_writer_can_access_dashboard_without_admin_menu(): void
    {
        $writer = User::factory()->create();

        $this->actingAs($writer)
            ->get('/cms/dashboard')
            ->assertOk()
            ->assertSee('Kewenangan Writer')
            ->assertDontSee('Manajemen Pengguna');
    }

    public function test_writer_cannot_access_admin_only_pages(): void
    {
        $writer = User::factory()->create();

        foreach (['/cms/pengguna', '/cms/pengaturan', '/cms/profil-organisasi', '/cms/pengurus'] as $url) {
            $this->actingAs($writer)->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_access_admin_only_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['/cms/pengguna', '/cms/pengaturan', '/cms/profil-organisasi', '/cms/pengurus'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_both_roles_can_access_content_modules(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            foreach (['/cms/berita', '/cms/media'] as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }

    public function test_writer_does_not_see_organization_menu(): void
    {
        $this->actingAs(User::factory()->create())->get('/cms/dashboard')
            ->assertDontSee('Struktur Pengurus')
            ->assertDontSee('Pengaturan Website');
    }

    public function test_categories_module_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create())->get('/cms/kategori')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/cms/kategori')->assertOk();
    }

    public function test_inactive_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get('/cms/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_run_livewire_actions_on_open_page(): void
    {
        $writer = User::factory()->create();
        $control = $this->draftArticleFor($writer);
        $target = $this->draftArticleFor($writer);

        $this->actingAs($writer);
        $snapshot = $this->livewireSnapshot(route('cms.articles'), 'cms.articles');

        $this->callLivewire($snapshot, 'publish', [$control->id])->assertOk();
        $this->assertSame(ArticleStatus::Published, $control->refresh()->status);

        $writer->update(['is_active' => false]);

        $this->callLivewire($snapshot, 'publish', [$target->id])->assertRedirect(route('login'));
        $this->assertSame(ArticleStatus::Draft, $target->refresh()->status);
        $this->assertGuest();
    }

    private function draftArticleFor(User $user): Article
    {
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => ArticleStatus::Draft, 'published_at' => null]);
        $article->categories()->attach(Category::factory()->create());

        return $article;
    }

    private function livewireSnapshot(string $url, string $component): string
    {
        preg_match_all('/wire:snapshot="([^"]*)"/', $this->get($url)->assertOk()->getContent(), $matches);

        foreach ($matches[1] as $raw) {
            $json = html_entity_decode($raw, ENT_QUOTES);
            if ((json_decode($json, true)['memo']['name'] ?? null) === $component) {
                return $json;
            }
        }

        $this->fail("Snapshot komponen {$component} tidak ditemukan.");
    }

    private function callLivewire(string $snapshot, string $method, array $params): TestResponse
    {
        return $this->withHeader('X-Livewire', 'true')->postJson(app(HandleRequests::class)->getUpdateUri(), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['path' => '', 'method' => $method, 'params' => $params]],
            ]],
        ]);
    }
}