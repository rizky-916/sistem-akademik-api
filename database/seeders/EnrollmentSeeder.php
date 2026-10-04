<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Students;
use App\Models\Courses; // Pastikan menggunakan 'Courses' bukan 'Course'
use Illuminate\Support\Str;

class EnrollmentSeeder extends Seeder
{
    public function run()
    {
        // Kosongkan tabel terlebih dahulu
        Enrollment::truncate();

        // Ambil beberapa student dan course
        $students = Students::take(10)->get();
        $courses = Courses::take(5)->get();

        // Status dan grade yang mungkin
        $statuses = ['active', 'completed', 'dropped'];
        $grades = ['A', 'B', 'C', 'D', 'E', null];
        $attendances = ['90%', '80%', '75%', '60%', '50%', null];

        // Buat data enrollment
        foreach ($students as $student) {
            foreach ($courses as $course) { // Ubah $courses menjadi $course
                // Pastikan tidak duplikat enrollment
                $existing = Enrollment::where('student_id', $student->student_id)
                                    ->where('course_id', $course->course_id)
                                    ->exists();
                
                if (!$existing) {
                    Enrollment::create([
                        'enrollment_id' => (string) Str::ulid(),
                        'student_id' => $student->student_id,
                        'course_id' => $course->course_id, // Ubah $courses menjadi $course
                        'status' => $statuses[array_rand($statuses)],
                        'grade' => $grades[array_rand($grades)],
                        'attendance' => $attendances[array_rand($attendances)],
                    ]);
                }
            }
        }
        

        // Buat beberapa enrollment tambahan secara acak
        for ($i = 0; $i < 15; $i++) {
            $student = Students::inRandomOrder()->first();
            $course = Courses::inRandomOrder()->first(); // Ubah $courses menjadi $course

            $existing = Enrollment::where('student_id', $student->student_id)
                                ->where('course_id', $course->course_id) // Ubah $courses menjadi $course
                                ->exists();

            if (!$existing) {
                Enrollment::create([
                    'enrollment_id' => (string) Str::ulid(),
                    'student_id' => $student->student_id,
                    'course_id' => $course->course_id, // Ubah $courses menjadi $course
                    'status' => $statuses[array_rand($statuses)],
                    'grade' => $grades[array_rand($grades)],
                    'attendance' => $attendances[array_rand($attendances)],
                ]);
            }
        }
    }
}