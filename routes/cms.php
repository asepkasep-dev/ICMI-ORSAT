<?php

use App\Livewire\Cms\Articles\Form;
use App\Livewire\Cms\Articles\Index;
use App\Livewire\Cms\Categories\Index as CategoryIndex;
use App\Livewire\Cms\Dashboard;
use App\Livewire\Cms\Management\Index as ManagementIndex;
use App\Livewire\Cms\Media\Index as MediaIndex;
use App\Livewire\Cms\Profile\Edit as ProfileEdit;
use App\Livewire\Cms\Settings\Edit as SettingsEdit;
use App\Livewire\Cms\Users\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])
    ->prefix('cms')
    ->name('cms.')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        Route::get('/berita', Index::class)->name('articles');
        Route::get('/berita/buat', Form::class)->name('articles.create');
        Route::get('/berita/{article}/edit', Form::class)->name('articles.edit');

        Route::get('/media', MediaIndex::class)->name('media');

        Route::middleware('role:admin')->group(function () {
            Route::get('/kategori', CategoryIndex::class)->name('categories');

            Route::get('/profil-organisasi', ProfileEdit::class)->name('profile');
            Route::get('/pengurus', ManagementIndex::class)->name('management');

            Route::get('/pengguna', UserIndex::class)->name('users');

            Route::get('/pengaturan', SettingsEdit::class)->name('settings');
        });
    });
