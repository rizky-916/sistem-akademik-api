<?php

namespace App\Http\Controllers;

use App\Models\Lecturers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class LecturersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $lecturers = Lecturers::orderBy('created_at', 'desc')->get();
        return response()->json($lecturers, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'NIP' => 'required|string|max:20|unique:lecturers,NIP',
            'department' => 'required|string|max:100',
            'email' => 'required|email|unique:lecturers,email',
        ]);

        $lecturer = Lecturers::create([
            'lecturer_id' => (string) Str::ulid(),
            'name' => $request->name,
            'NIP' => $request->NIP,
            'department' => $request->department,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Lecturers created successfully.',
            'data' => $lecturer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $lecturer = Lecturers::findOrFail($id);
            return response()->json($lecturer, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lecturers not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $lecturer = Lecturers::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'NIP' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('lecturers')->ignore($lecturer->lecturer_id, 'lecturer_id')
                ],
                'department' => 'sometimes|string|max:100',
                'email' => [
                    'sometimes',
                    'email',
                    Rule::unique('lecturers')->ignore($lecturer->lecturer_id, 'lecturer_id')
                ],
            ]);

            $data = $request->only(['name', 'NIP', 'department', 'email']);
            $lecturer->update($data);

            return response()->json([
                'message' => $lecturer->wasChanged()
                    ? 'Lecturers updated successfully.'
                    : 'No changes were made to the lecturer data.',
                'data' => $lecturer
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lecturers not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $lecturer = Lecturers::findOrFail($id);
            $lecturer->delete();

            return response()->json(['message' => 'Lecturers deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lecturers not found'], 404);
        }
    }
}