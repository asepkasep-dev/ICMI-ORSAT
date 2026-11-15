<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->guardProductionPasswords();

        // Akun operasional awal; identitas Admin resmi belum dikonfirmasi (PRD §12).
        // Password awal dari env (SEED_ADMIN_PASSWORD/SEED_WRITER_PASSWORD);
        // wajib diganti setelah login pertama dan sebelum produksi.
        User::firstOrCreate(
            ['email' => 'admin@icmicaringin.or.id'],
            [
                'name' => 'Administrator',
                'password' => config('icmi.seed.admin_password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'writer@icmicaringin.or.id'],
            [
                'name' => 'Penulis ICMI',
                'password' => config('icmi.seed.writer_password'),
                'role' => UserRole::Writer,
                'is_active' => true,
            ]
        );

        $this->call(DemoContentSeeder::class);
    }

    private function guardProductionPasswords(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        foreach (['admin_password' => 'SEED_ADMIN_PASSWORD', 'writer_password' => 'SEED_WRITER_PASSWORD'] as $key => $env) {
            $password = (string) config("icmi.seed.{$key}");

            if ($password === 'password' || mb_strlen($password) < 12) {
                throw new RuntimeException("Isi {$env} di .env dengan password kuat (minimal 12 karakter) sebelum menjalankan seeder di produksi.");
            }
        }
    }
}