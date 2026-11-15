<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke CMS');
    }

    public function test_admin_can_login(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::test(Login::class)
            ->set('email', $admin->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_writer_can_login(): void
    {
        $writer = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $writer->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.dashboard'));

        $this->assertAuthenticatedAs($writer);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Login::class)
                ->set('email', $user->email)
                ->set('password', 'wrong-password')
                ->call('authenticate')
                ->assertHasErrors('email');
        }

        $response = Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak', $response->errors()->first('email'));
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        Livewire::test(Login::class)
            ->call('authenticate')
            ->assertHasErrors(['email', 'password']);
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}