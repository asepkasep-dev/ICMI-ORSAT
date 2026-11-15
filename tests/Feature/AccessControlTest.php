<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs($writer)->get('/cms/pengguna')->assertForbidden();
        $this->actingAs($writer)->get('/cms/pengaturan')->assertForbidden();
    }

    public function test_admin_can_access_admin_only_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/cms/pengguna')->assertOk();
        $this->actingAs($admin)->get('/cms/pengaturan')->assertOk();
    }

    public function test_both_roles_can_access_content_modules(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->create()] as $user) {
            foreach (['/cms/berita', '/cms/kategori', '/cms/media', '/cms/profil-organisasi', '/cms/pengurus'] as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }

    public function test_inactive_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get('/cms/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}