<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    public function authenticate(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', 'Email atau password salah.');

            return;
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->addError('email', 'Akun Anda dinonaktifkan. Hubungi Admin untuk informasi lebih lanjut.');

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirectIntended(route('cms.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.auth');
    }
}