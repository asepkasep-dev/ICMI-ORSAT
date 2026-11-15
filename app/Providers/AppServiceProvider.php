<?php

namespace App\Providers;

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('components.pagination');

        // Tanpa ini, request update Livewire melewati cek akun aktif dan peran pada halaman yang sudah terbuka.
        Livewire::addPersistentMiddleware([
            EnsureUserIsActive::class,
            EnsureRole::class,
        ]);
    }
}
