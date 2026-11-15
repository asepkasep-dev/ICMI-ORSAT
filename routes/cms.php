<?php

use App\Livewire\Cms\Articles\Form;
use App\Livewire\Cms\Articles\Index;
use App\Livewire\Cms\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])
    ->prefix('cms')
    ->name('cms.')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        Route::get('/berita', Index::class)->name('articles');
        Route::get('/berita/buat', Form::class)->name('articles.create');
        Route::get('/berita/{article}/edit', Form::class)->name('articles.edit');

        Route::get('/kategori', fn () => view('cms.coming-soon', [
            'title' => 'Kategori Berita',
            'milestone' => 'Milestone 3 — Content Management System',
            'description' => 'Pengelolaan kategori berita oleh Admin.',
        ]))->name('categories');

        Route::get('/media', fn () => view('cms.coming-soon', [
            'title' => 'Media Library',
            'milestone' => 'Milestone 3 — Content Management System',
            'description' => 'Unggah, pilih, dan kelola gambar berita dengan validasi dan optimasi WebP.',
        ]))->name('media');

        Route::get('/profil-organisasi', fn () => view('cms.coming-soon', [
            'title' => 'Profil Organisasi',
            'milestone' => 'Milestone 4 — Organization Profile',
            'description' => 'Pengelolaan sejarah, visi, misi, tujuan, dan sambutan Ketua.',
        ]))->name('profile');

        Route::get('/pengurus', fn () => view('cms.coming-soon', [
            'title' => 'Struktur Pengurus',
            'milestone' => 'Milestone 4 — Organization Profile',
            'description' => 'Pengelolaan data pengurus, jabatan, bidang, foto, dan urutan tampilan.',
        ]))->name('management');

        Route::middleware('role:admin')->group(function () {
            Route::get('/pengguna', fn () => view('cms.coming-soon', [
                'title' => 'Manajemen Pengguna',
                'milestone' => 'Milestone 3 — Content Management System',
                'description' => 'Pembuatan dan pengelolaan akun Writer oleh Admin.',
            ]))->name('users');

            Route::get('/pengaturan', fn () => view('cms.coming-soon', [
                'title' => 'Pengaturan Website',
                'milestone' => 'Milestone 4 — Organization Profile',
                'description' => 'Identitas website, kontak, media sosial, dan SEO dasar.',
            ]))->name('settings');
        });
    });