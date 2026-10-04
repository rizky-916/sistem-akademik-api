<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Students;
use App\Models\Courses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class EnrollmentController extends Controller
{
    public function index(): JsonResponse
    {
        $enrollments = Enrollment::with(['students', 'courses'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return response()->json($enrollments, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $enrollment = Enrollment::with(['students', 'courses'])->findOrFail($id);
            return response()->json($enrollment, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'course_id' => 'required|exists:courses,course_id',
            'status' => 'required|string|in:active,completed,dropped',
            'grade' => 'nullable|string|max:2',
            'attendance' => 'nullable|string|max:10',
        ]);

        // Check for existing enrollment
        $existing = Enrollment::where('student_id', $request->student_id)
                            ->where('course_id', $request->course_id)
                            ->exists();
                            
        if ($existing) {
            return response()->json([
                'message' => 'Students is already enrolled in this course.'
            ], 409);
        }

        $enrollment = Enrollment::create([
            'enrollment_id' => (string) Str::ulid(),
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'status' => $request->status,
            'grade' => $request->grade,
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'message' => 'Enrollment created successfully.',
            'data' => $enrollment
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $enrollment = Enrollment::findOrFail($id);

            $request->validate([
                'student_id' => 'sometimes|exists:students,student_id',
                'course_id' => [
                    'sometimes',
                    'exists:courses,course_id',
                    Rule::unique('enrollments')
                        ->where('student_id', $request->student_id ?? $enrollment->student_id)
                        ->ignore($enrollment->enrollment_id, 'enrollment_id')
                ],
                'status' => 'sometimes|string|in:active,completed,dropped',
                'grade' => 'sometimes|string|max:2',
                'attendance' => 'sometimes|string|max:10',
            ]);

            $data = $request->only(['student_id', 'course_id', 'status', 'grade', 'attendance']);
            $enrollment->update($data);

            return response()->json([
                'message' => $enrollment->wasChanged()
                    ? 'Enrollment updated successfully.'
                    : 'No changes were made to the enrollment.',
                'data' => $enrollment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $enrollment = Enrollment::findOrFail($id);
            $enrollment->delete();

            return response()->json(['message' => 'Enrollment deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    }

    public function updateGrade(Request $request, $id): JsonResponse
    {
        try {
            $enrollment = Enrollment::findOrFail($id);

            $request->validate([
                'grade' => 'required|string|max:2',
            ]);

            $enrollment->update(['grade' => $request->grade]);

            return response()->json([
                'message' => 'Grade updated successfully.',
                'data' => $enrollment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    }

    public function updateAttendance(Request $request, $id): JsonResponse
    {
        try {
            $enrollment = Enrollment::findOrFail($id);

            $request->validate([
                'attendance' => 'required|string|max:10',
            ]);

            $enrollment->update(['attendance' => $request->attendance]);

            return response()->json([
                'message' => 'Attendance updated successfully.',
                'data' => $enrollment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    }
}