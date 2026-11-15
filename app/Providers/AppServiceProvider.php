<?php

namespace App\Providers;

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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

        // Di belakang reverse proxy/SSL terminator, URL yang dibangkitkan tetap https.
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Tanpa ini, request update Livewire melewati cek akun aktif dan peran pada halaman yang sudah terbuka.
        Livewire::addPersistentMiddleware([
            EnsureUserIsActive::class,
            EnsureRole::class,
        ]);
    }
}
