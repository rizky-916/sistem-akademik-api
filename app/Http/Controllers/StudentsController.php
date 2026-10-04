<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;

use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentsController extends Controller
{
    public function index(): JsonResponse
    {
        $students = Students::all();
        return response()->json($students, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $students = Students::findOrFail($id);
            return response()->json($students, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Students not found'], 404);
        }
    }

    public function store(Request $request): JsonResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:students,email',
        'NIM' => 'required|string|size:9|unique:students,NIM',
        'major' => 'required|string|max:255',
        'enrollment_year' => 'required|regex:/^\d{4}$/', // Only 4 digits for the year
    ]);

    $students = Students::create([
        'name' => $request->name,
        'email' => $request->email,
        'NIM' => $request->NIM,
        'major' => $request->major,
        'enrollment_year' => $request->enrollment_year,
    ]);

    return response()->json([
        'message' => 'Students successfully added.',
        'data' => $students
    ], 201);
}

public function update(Request $request, $id): JsonResponse
{
    try {
        $students = Students::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('students', 'email')->ignore($students->student_id, 'student_id')],
            'NIM' => ['sometimes', 'string', 'size:9', Rule::unique('students', 'NIM')->ignore($students->student_id, 'student_id')],
            'major' => 'sometimes|string|max:255',
            'enrollment_year' => 'sometimes|regex:/^\d{4}$/', // Only 4 digits for the year
        ]);

        $data = $request->only(['name', 'email', 'NIM', 'major', 'enrollment_year']);
        $students->update($data);

        return response()->json([
            'message' => $students->wasChanged()
                ? 'Students successfully updated.'
                : 'No changes made to students data.',
            'data' => $students
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Students not found'], 404);
    }
}
public function destroy($id): JsonResponse
{
    try {
        $students = Students::findOrFail($id);
        $students->delete();

        return response()->json(['message' => 'Students successfully deleted.']);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Students not found.'], 404);
    }
}

}