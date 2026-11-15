<?php

namespace Tests\Feature;

use App\Livewire\Cms\Categories\Index as CategoryIndex;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent_on_public_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'")
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_only_for_secure_production_requests(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_security_headers_are_sent_on_redirects_and_errors(): void
    {
        $this->get('/cms/dashboard')->assertRedirect('/login')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->get('/berita/tidak-ada')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_seeder_refuses_default_passwords_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['icmi.seed.admin_password' => 'password', 'icmi.seed.writer_password' => 'password']);

        $this->expectException(RuntimeException::class);

        try {
            $this->runSeeder();
        } finally {
            $this->assertSame(0, User::count());
        }
    }

    public function test_seeder_accepts_strong_passwords_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'icmi.seed.admin_password' => 'Admin-Kuat-2026!',
            'icmi.seed.writer_password' => 'Writer-Kuat-2026!',
            'icmi.demo_mode' => false,
        ]);

        $this->runSeeder();

        $this->assertSame(2, User::count());
    }

    // db:seed meminta konfirmasi interaktif di produksi, jadi seeder dipanggil langsung.
    private function runSeeder(): void
    {
        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->__invoke();
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $admin = User::factory()->admin()->create();

        $component = Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->call('openCreate')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertSame('Nama kategori wajib diisi.', $component->errors()->first('name'));
        $component->assertSee('Nama kategori wajib diisi.');
    }
}
