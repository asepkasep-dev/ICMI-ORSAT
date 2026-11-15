<?php

namespace Tests\Feature\Cms;

use App\Enums\UserRole;
use App\Livewire\Cms\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_page_is_admin_only(): void
    {
        $this->get('/cms/pengguna')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/cms/pengguna')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/cms/pengguna')->assertOk()->assertSee('Manajemen Pengguna');
    }

    public function test_admin_can_create_writer_account(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('name', 'Writer Baru')
            ->set('email', 'writer.baru@icmicaringin.or.id')
            ->set('roleValue', 'writer')
            ->set('password', 'rahasia-kuat-123')
            ->set('password_confirmation', 'rahasia-kuat-123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false);

        $user = User::where('email', 'writer.baru@icmicaringin.or.id')->firstOrFail();
        $this->assertSame(UserRole::Writer, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('rahasia-kuat-123', $user->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'user.created', 'subject_id' => $user->id]);
    }

    public function test_create_requires_valid_unique_email_and_confirmed_password(): void
    {
        User::factory()->create(['email' => 'sudah.ada@icmicaringin.or.id']);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('name', 'Ab')
            ->set('email', 'sudah.ada@icmicaringin.or.id')
            ->set('password', 'pendek')
            ->set('password_confirmation', 'beda')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password']);
    }

    public function test_admin_can_edit_user_without_changing_password(): void
    {
        $target = User::factory()->create(['name' => 'Nama Asli', 'password' => 'password-lama']);
        $oldHash = $target->password;
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openEdit', $target->id)
            ->set('name', 'Nama Diperbarui')
            ->set('email', 'email.baru@icmicaringin.or.id')
            ->call('save')
            ->assertHasNoErrors();

        $target->refresh();
        $this->assertSame('Nama Diperbarui', $target->name);
        $this->assertSame('email.baru@icmicaringin.or.id', $target->email);
        $this->assertSame($oldHash, $target->password);
    }

    public function test_admin_can_reset_password(): void
    {
        $target = User::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('openEdit', $target->id)
            ->set('password', 'sandi-baru-456')
            ->set('password_confirmation', 'sandi-baru-456')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('sandi-baru-456', $target->refresh()->password));
    }

    public function test_admin_can_deactivate_and_reactivate_user(): void
    {
        $target = User::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('toggleActive', $target->id)->assertDispatched('toast');
        $this->assertFalse($target->refresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user.deactivated']);

        // Akun nonaktif tidak bisa mengakses CMS.
        $this->actingAs($target)->get('/cms/dashboard')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(Index::class)->call('toggleActive', $target->id);
        $this->assertTrue($target->refresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user.activated']);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('toggleActive', $admin->id)
            ->assertDispatched('toast', type: 'error');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(); // writer, bukan admin
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('toggleActive', $admin->id)
            ->assertDispatched('toast', type: 'error');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->admin()->create();
        // Admin nonaktif tetap bisa memanggil komponen secara langsung (uji pertahanan berlapis);
        // ia tidak dihitung sebagai admin aktif lain.
        $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
        $this->actingAs($inactiveAdmin);

        Livewire::test(Index::class)
            ->call('openEdit', $admin->id)
            ->set('roleValue', 'writer')
            ->call('save')
            ->assertHasErrors(['roleValue']);

        $this->assertSame(UserRole::Admin, $admin->refresh()->role);
    }

    public function test_self_edit_cannot_change_own_role_or_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create(); // admin lain agar guard tidak menghalangi
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openEdit', $admin->id)
            ->set('name', 'Nama Saya Baru')
            ->set('roleValue', 'writer')
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertSame('Nama Saya Baru', $admin->name);
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_writer_cannot_manage_users_via_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertStatus(403);
    }
}