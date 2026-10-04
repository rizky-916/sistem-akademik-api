<?php

namespace App\Http\Controllers;

use App\Models\CoursesLecturers;
use App\Models\Courses;
use App\Models\Lecturers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CoursesLecturersController extends Controller
{
    public function index(): JsonResponse
    {
        $assignments = CoursesLecturers::with(['course', 'lecturer'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return response()->json($assignments, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $assignment = CoursesLecturers::with(['course', 'lecturer'])->findOrFail($id);
            return response()->json($assignment, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Assignment tidak ditemukan'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|ulid|exists:courses,course_id',
            'lecturer_id' => 'required|ulid|exists:lecturers,lecturer_id',
            'role' => 'required|string|max:50',
        ]);

        // Check for existing assignment
        $exists = CoursesLecturers::where('course_id', $request->course_id)
                                ->where('lecturer_id', $request->lecturer_id)
                                ->where('role', $request->role)
                                ->exists();
                                
        if ($exists) {
            return response()->json([
                'message' => 'Assignment ini sudah ada'
            ], 409);
        }

        $assignment = CoursesLecturers::create([
            'course_id' => $request->course_id,
            'lecturer_id' => $request->lecturer_id,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Assignment berhasil ditambahkan',
            'data' => $assignment->load(['course', 'lecturer'])
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $assignment = CoursesLecturers::findOrFail($id);

            $request->validate([
                'course_id' => 'sometimes|required|ulid|exists:courses,course_id',
                'lecturer_id' => 'sometimes|required|ulid|exists:lecturers,lecturer_id',
                'role' => 'sometimes|required|string|max:50',
            ]);

            // Check for duplicate assignments
            $exists = CoursesLecturers::where('course_id', $request->input('course_id', $assignment->course_id))
                                    ->where('lecturer_id', $request->input('lecturer_id', $assignment->lecturer_id))
                                    ->where('role', $request->input('role', $assignment->role))
                                    ->where('id', '!=', $id)
                                    ->exists();
            
            if ($exists) {
                return response()->json([
                    'message' => 'Kombinasi assignment ini sudah ada'
                ], 409);
            }

            $data = $request->only(['course_id', 'lecturer_id', 'role']);
            $assignment->update($data);

            return response()->json([
                'message' => $assignment->wasChanged()
                    ? 'Assignment berhasil diupdate'
                    : 'Tidak ada perubahan pada assignment',
                'data' => $assignment->fresh(['course', 'lecturer'])
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Assignment tidak ditemukan'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $assignment = CoursesLecturers::findOrFail($id);
            $assignment->delete();

            return response()->json(['message' => 'Assignment berhasil dihapus']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Assignment tidak ditemukan'], 404);
        }
    }

    public function getLecturersForCourse($courseId): JsonResponse
    {
        try {
            $course = Courses::findOrFail($courseId);
            $lecturers = $course->lecturers()
                              ->withPivot('role')
                              ->get();
                              
            return response()->json($lecturers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Course tidak ditemukan'], 404);
        }
    }

    public function getCoursesForLecturer($lecturerId): JsonResponse
    {
        try {
            $lecturer = Lecturers::findOrFail($lecturerId);
            $courses = $lecturer->courses()
                              ->withPivot('role')
                              ->get();
                              
            return response()->json($courses, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lecturer tidak ditemukan'], 404);
        }
    }
}