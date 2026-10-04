<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\CoursesLecturersController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LecturersController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Publik: hanya login, dibatasi 5 percobaan per menit per IP (limiter "login")
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Semua endpoint lain wajib token Sanctum
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Akun pengguna (POST /api/user juga menggantikan /api/user/store)
    Route::post('/user/store', [UserController::class, 'store']);
    Route::apiResource('user', UserController::class);

    Route::apiResource('student', StudentsController::class);
    Route::apiResource('courses', CoursesController::class);
    Route::apiResource('lecturers', LecturersController::class);
    Route::apiResource('enrollment', EnrollmentController::class);
    Route::apiResource('courseslecturers', CoursesLecturersController::class);
});
