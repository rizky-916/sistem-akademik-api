<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $dataUser = User::all();
        return response()->json($dataUser, 200);
    }

    // Menampilkan user berdasarkan ID
    public function show($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    // Menambahkan user baru
    public function store(Request $request): JsonResponse
    {
        // Validasi data yang dikirim
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // Pastikan password memiliki konfirmasi
        ]);

        // Membuat user baru dengan data yang sudah tervalidasi
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'], // di-hash otomatis oleh cast 'hashed' di model User
        ]);

        // Mengembalikan response dengan pesan sukses
        return response()->json([
            'message' => 'Akun pengguna berhasil ditambahkan.',
            'data' => $user
        ], 201);
    }

    // Mengupdate data user
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Validasi input yang dapat diupdate
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'username' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
                'password' => 'sometimes|string|min:8',
            ]);

            // Hanya update field yang sudah lolos validasi (password di-hash otomatis oleh model)
            $data = collect($validated)->only(['name', 'email', 'password', 'username'])->all();

            $user->update($data);

            // Mengembalikan response jika ada perubahan atau tidak
            return response()->json([
                'message' => $user->wasChanged()
                    ? 'Akun pengguna berhasil diupdate.'
                    : 'Tidak ada perubahan pada data pengguna.',
                'data' => $user
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    // Menghapus user
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Cegah pengguna menghapus akunnya sendiri yang sedang dipakai login
            if (request()->user()?->is($user)) {
                return response()->json(['message' => 'Tidak dapat menghapus akun yang sedang digunakan.'], 422);
            }

            $user->tokens()->delete();
            $user->delete();

            return response()->json(['message' => 'User berhasil dihapus.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }
    }
}
