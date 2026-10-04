<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password admin diambil dari .env agar tidak pernah tertulis di kode sumber
        $password = config('app.seed_admin_password');

        if (! is_string($password) || strlen($password) < 8) {
            throw new RuntimeException(
                'Isi SEED_ADMIN_PASSWORD di file .env (minimal 8 karakter) sebelum menjalankan seeder.'
            );
        }

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => $password, // di-hash otomatis oleh cast 'hashed'
                'email_verified_at' => now(),
            ]
        );
    }
}
