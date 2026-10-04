<?php

namespace Database\Seeders;

use App\Models\Students;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Menjalankan database seeds.
     */
    public function run(): void
    {
        Students::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'NIM' => '221000001',
            'major' => 'Computer Science',
            // Menggunakan tahun sebagai string
            'enrollment_year' => Carbon::now()->subYears(2)->year, // Mengambil tahun (misalnya, '2022')
        ]);

        // Contoh data mahasiswa tambahan
        Students::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'NIM' => '221000002',
            'major' => 'Information Systems',
            // Menggunakan tahun sebagai string
            'enrollment_year' => Carbon::now()->subYear()->year, // Mengambil tahun (misalnya, '2023')
        ]);
    }
}
