<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoursesLecturers;
use App\Models\Courses;
use App\Models\Lecturers;
use Illuminate\Support\Str;

class CoursesLecturersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        CoursesLecturers::truncate();

        // Get all courses and lecturers
        $courses = Courses::all();
        $lecturers = Lecturers::all();

        // Ensure we have data to work with
        if ($courses->isEmpty() || $lecturers->isEmpty()) {
            $this->command->error('Please seed Courses and Lecturers first!');
            return;
        }

        // Define possible roles
        $roles = [
            'Main Lecturer',
            'Co-Lecturer', 
            'Teaching Assistant',
            'Guest Lecturer'
        ];

        // Create assignments
        foreach ($courses as $course) {
            // Assign 1-3 lecturers to each course (tidak melebihi jumlah dosen yang tersedia)
            $assignedLecturers = $lecturers->random(rand(1, min(3, $lecturers->count())));
            
            foreach ($assignedLecturers as $lecturer) {
                $role = $roles[array_rand($roles)];
                
                // Ensure unique assignment
                $exists = CoursesLecturers::where('course_id', $course->course_id)
                    ->where('lecturer_id', $lecturer->lecturer_id)
                    ->where('role', $role)
                    ->exists();

                if (!$exists) {
                    CoursesLecturers::create([
                        'course_id' => $course->course_id,
                        'lecturer_id' => $lecturer->lecturer_id,
                        'role' => $role,
                    ]);
                }
            }
        }

        $this->command->info('Successfully seeded courses_lecturers table!');
        $this->command->info('Total assignments: '.CoursesLecturers::count());
    }
}