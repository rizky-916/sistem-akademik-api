<?php

namespace Database\Seeders;

use App\Models\Courses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Courses::create([
            'name' => 'Introduction to Programming',
            'code' => 'CS101',
            'credits' => 3,
            'semester' => '1',
        ]);

        Courses::create([
            'name' => 'Database Systems',
            'code' => 'CS201',
            'credits' => 4,
            'semester' => '2',
        ]);
    }
}