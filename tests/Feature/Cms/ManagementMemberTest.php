<?php

namespace Tests\Feature\Cms;

use App\Livewire\Cms\Management\Index;
use App\Models\ManagementMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagementMemberTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attrs = []): ManagementMember
    {
        return ManagementMember::create(array_merge([
            'name' => 'Anggota',
            'position' => 'Anggota',
            'sort_order' => 1,
            'is_active' => true,
        ], $attrs));
    }

    public function test_writer_cannot_run_management_component(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertForbidden();
    }

    public function test_admin_creates_member_and_it_appears_publicly(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->member(['sort_order' => 4]);

        Livewire::test(Index::class)
            ->call('openCreate')
            ->assertSet('sort_order', '5')
            ->set('name', 'Nama Pengurus Uji')
            ->set('position', 'Ketua Bidang')
            ->set('division', '  Pendidikan ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast')
            ->assertSet('formOpen', false);

        $this->assertDatabaseHas('management_members', ['name' => 'Nama Pengurus Uji', 'division' => 'Pendidikan', 'sort_order' => 5]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'member.created']);

        auth()->logout();
        $this->get('/struktur-kepengurusan')->assertOk()
            ->assertSee('Nama Pengurus Uji')
            ->assertSee('Bidang Pendidikan');
    }

    public function test_empty_division_is_stored_as_core_and_validation_applies(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $member = $this->member(['division' => 'Lama']);

        Livewire::test(Index::class)
            ->call('openEdit', $member->id)
            ->set('division', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($member->refresh()->division);

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('name', '')
            ->set('position', '')
            ->set('sort_order', '-1')
            ->call('save')
            ->assertHasErrors(['name', 'position', 'sort_order']);
    }

    public function test_toggle_hides_member_from_public_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $member = $this->member(['name' => 'Pengurus Tersembunyi']);

        Livewire::test(Index::class)->call('toggleActive', $member->id);

        $this->assertFalse($member->refresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'member.deactivated']);

        auth()->logout();
        $this->get('/struktur-kepengurusan')->assertDontSee('Pengurus Tersembunyi');
    }

    public function test_move_swaps_within_same_division_and_renumbers(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $a = $this->member(['name' => 'A', 'sort_order' => 10]);
        $other = $this->member(['name' => 'X', 'division' => 'Dakwah', 'sort_order' => 15]);
        $b = $this->member(['name' => 'B', 'sort_order' => 20]);

        Livewire::test(Index::class)->call('move', $b->id, 'up');

        $this->assertSame(['B', 'X', 'A'], ManagementMember::orderBy('sort_order')->pluck('name')->all());
        $this->assertSame([1, 2, 3], ManagementMember::orderBy('sort_order')->pluck('sort_order')->map(fn ($v) => (int) $v)->all());

        Livewire::test(Index::class)->call('move', $b->id, 'up');
        $this->assertSame(1, (int) $b->refresh()->sort_order);
    }

    public function test_delete_removes_member(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $member = $this->member();

        Livewire::test(Index::class)
            ->call('confirmDelete', $member->id)
            ->assertSet('deleteId', $member->id)
            ->call('deleteConfirmed');

        $this->assertModelMissing($member);
        $this->assertDatabaseHas('activity_logs', ['action' => 'member.deleted']);
    }
}
