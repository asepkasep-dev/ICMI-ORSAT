<?php

return [
    'name' => 'ICMI Kecamatan Caringin',
    'short_name' => 'ICMI Caringin',
    'long_name' => 'Ikatan Cendekiawan Muslim se-Indonesia — Kecamatan Caringin',
    'tagline' => 'Pusat Informasi dan Publikasi Resmi Organisasi',
    'term' => '2026–2031',
    'description' => 'Website resmi ICMI Kecamatan Caringin: profil organisasi, struktur kepengurusan, berita, dan kegiatan.',

    // Mode pratinjau desain: memuat konten contoh berlabel (bukan data resmi).
    // Produksi harus false — data resmi dikelola melalui CMS (PRD §12).
    'demo_mode' => (bool) env('ICMI_DEMO_MODE', false),

    // Batas unggah gambar (PRD §5.5). Di atas 12 MB, batas upload sementara Livewire
    // (livewire.temporary_file_upload.rules) dan upload_max_filesize PHP juga harus dinaikkan.
    'uploads' => [
        'max_kb' => (int) env('UPLOAD_MAX_KB', 2048),
        'max_files' => (int) env('UPLOAD_MAX_FILES', 10),
    ],

    // Password awal akun seeder; wajib di-override lewat env sebelum produksi (PRD §9).
    'seed' => [
        'admin_password' => env('SEED_ADMIN_PASSWORD') ?: 'password',
        'writer_password' => env('SEED_WRITER_PASSWORD') ?: 'password',
    ],

    // Cadangan basis data + media (php artisan icmi:backup). Folder ini tidak dapat diakses publik.
    'backup' => [
        'path' => env('BACKUP_PATH') ?: storage_path('app/backups'),
        'keep' => (int) env('BACKUP_KEEP', 14),
        'exclude_tables' => [
            'migrations', 'cache', 'cache_locks', 'sessions',
            'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens',
        ],
    ],

    // Nilai null berarti data resmi belum tersedia (PRD §12 — jangan diisi data rekaan).
    'contact' => [
        'address' => null,
        'email' => null,
        'phone' => null,
    ],

    'social' => [
        'instagram' => null,
        'facebook' => null,
        'youtube' => null,
        'tiktok' => null,
    ],

    // Diisi Admin melalui Pengaturan Website bila diperlukan (PRD §8).
    'seo' => [
        'google_site_verification' => null,
        'google_analytics_id' => null,
    ],
];