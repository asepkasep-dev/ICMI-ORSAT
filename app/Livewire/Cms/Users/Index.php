<?php

namespace App\Livewire\Cms\Users;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $role = '';

    public bool $formOpen = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $roleValue = 'writer';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $isActive = true;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);

        $this->reset(['editingId', 'name', 'email', 'password', 'password_confirmation']);
        $this->roleValue = 'writer';
        $this->isActive = true;
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function openEdit(int $id): void
    {
        $target = User::findOrFail($id);
        $this->authorize('update', $target);

        $this->editingId = $target->id;
        $this->name = $target->name;
        $this->email = $target->email;
        $this->roleValue = $target->role->value;
        $this->isActive = $target->is_active;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->reset(['password', 'password_confirmation']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $target = $this->editingId ? User::findOrFail($this->editingId) : null;
        $this->authorize($target ? 'update' : 'create', $target ?? User::class);

        $isSelf = $target && $target->id === auth()->id();

        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->editingId)],
            'roleValue' => ['required', Rule::in(['admin', 'writer'])],
            'password' => [$target ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'isActive' => ['boolean'],
        ], [], [
            'name' => 'nama',
            'email' => 'email',
            'roleValue' => 'peran',
            'password' => 'kata sandi',
            'isActive' => 'status aktif',
        ]);

        if ($isSelf) {
            $data['roleValue'] = $target->role->value;
            $data['isActive'] = true;
        } elseif ($target) {
            $losesAdmin = $target->isAdmin() && $target->is_active
                && ($data['roleValue'] !== 'admin' || ! $data['isActive']);

            if ($losesAdmin && $this->isLastActiveAdmin($target)) {
                throw ValidationException::withMessages([
                    'roleValue' => 'Minimal harus ada satu Admin aktif. Tambahkan Admin lain sebelum mengubah peran atau menonaktifkan akun ini.',
                ]);
            }
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['roleValue'],
            'is_active' => (bool) $data['isActive'],
        ];

        if ($data['password'] !== null && $data['password'] !== '') {
            $payload['password'] = $data['password'];
        }

        if ($target) {
            $target->update($payload);
            ActivityLog::record('user.updated', $target, 'Memperbarui akun "'.$target->name.'" ('.$target->role->label().', '.($target->is_active ? 'aktif' : 'nonaktif').')');
            $this->dispatch('toast', message: 'Akun "'.$target->name.'" diperbarui.');
        } else {
            $target = User::create($payload);
            ActivityLog::record('user.created', $target, 'Membuat akun "'.$target->name.'" sebagai '.$target->role->label());
            $this->dispatch('toast', message: 'Akun "'.$target->name.'" dibuat sebagai '.$target->role->label().'.');
        }

        $this->formOpen = false;
        $this->reset(['editingId', 'name', 'email', 'password', 'password_confirmation']);
    }

    public function toggleActive(int $id): void
    {
        $target = User::findOrFail($id);
        $this->authorize('update', $target);

        if ($target->id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $newState = ! $target->is_active;

        if (! $newState && $this->isLastActiveAdmin($target)) {
            $this->dispatch('toast', type: 'error', message: 'Minimal harus ada satu Admin aktif. Tambahkan Admin lain sebelum menonaktifkan akun ini.');

            return;
        }

        $target->update(['is_active' => $newState]);
        ActivityLog::record($newState ? 'user.activated' : 'user.deactivated', $target, ($newState ? 'Mengaktifkan' : 'Menonaktifkan').' akun "'.$target->name.'"');
        $this->dispatch('toast', message: 'Akun "'.$target->name.'" '.($newState ? 'diaktifkan' : 'dinonaktifkan').'.');
    }

    private function isLastActiveAdmin(User $target): bool
    {
        if (! $target->isAdmin() || ! $target->is_active) {
            return false;
        }

        return User::where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->where('id', '!=', $target->id)
            ->count() === 0;
    }

    public function render()
    {
        $query = User::withCount('articles')->orderBy('name');

        if ($this->q !== '') {
            $term = '%'.$this->q.'%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        if ($this->role !== '') {
            $query->where('role', $this->role);
        }

        return view('livewire.cms.users.index', [
            'users' => $query->paginate(10),
            'roles' => UserRole::cases(),
            'isSelf' => $this->editingId === auth()->id(),
        ])->layout('components.layouts.cms', ['title' => 'Manajemen Pengguna']);
    }
}