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

    // Password awal akun seeder; wajib di-override lewat env sebelum produksi (PRD §9).
    'seed' => [
        'admin_password' => env('SEED_ADMIN_PASSWORD') ?: 'password',
        'writer_password' => env('SEED_WRITER_PASSWORD') ?: 'password',
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
];