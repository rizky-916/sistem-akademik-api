<?php

namespace Database\Seeders;

use App\Models\Lecturers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LecturersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lecturers::create([
            'lecturer_id' => (string) Str::ulid(),
            'name' => 'Dr. John Doe',
            'NIP' => '000000000000000001',
            'department' => 'Computer Science',
            'email' => 'john.doe.lecturer@example.com',
        ]);

        Lecturers::create([
            'lecturer_id' => (string) Str::ulid(),
            'name' => 'Prof. Jane Smith',
            'NIP' => '000000000000000002',
            'department' => 'Information Technology',
            'email' => 'jane.smith.lecturer@example.com',
        ]);
    }
}