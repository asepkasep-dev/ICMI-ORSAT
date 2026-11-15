<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun operasional awal; identitas Admin resmi belum dikonfirmasi (PRD §12).
        // Password wajib diganti setelah login pertama.
        User::firstOrCreate(
            ['email' => 'admin@icmicaringin.or.id'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'writer@icmicaringin.or.id'],
            [
                'name' => 'Penulis ICMI',
                'password' => 'password',
                'role' => UserRole::Writer,
                'is_active' => true,
            ]
        );

        $this->call(DemoContentSeeder::class);
    }
}