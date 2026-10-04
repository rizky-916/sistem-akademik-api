<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;

use App\Models\Courses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CoursesController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = Courses::orderBy('created_at', 'desc')->get();
        return response()->json($courses, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $course = Courses::findOrFail($id);
            return response()->json($course, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Courses not found'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code',
            'credits' => 'required|integer|min:1|max:24',
            'semester' => 'required|string|max:20',
        ]);

        $course = Courses::create([
            'course_id' => (string) Str::ulid(),
            'name' => $request->name,
            'code' => $request->code,
            'credits' => $request->credits,
            'semester' => $request->semester,
        ]);

        return response()->json([
            'message' => 'Courses successfully created.',
            'data' => $course
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $course = Courses::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'code' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('courses')->ignore($course->course_id, 'course_id')
                ],
                'credits' => 'sometimes|integer|min:1|max:24',
                'semester' => 'sometimes|string|max:20',
            ]);

            $data = $request->only(['name', 'code', 'credits', 'semester']);
            $course->update($data);

            return response()->json([
                'message' => $course->wasChanged()
                    ? 'Courses successfully updated.'
                    : 'No changes made to course data.',
                'data' => $course
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Courses not found'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $course = Courses::findOrFail($id);
            $course->delete();

            return response()->json(['message' => 'Courses successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Courses not found.'], 404);
        }
    }
}