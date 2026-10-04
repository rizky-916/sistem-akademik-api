<?php

use App\Models\Courses;
use App\Models\Lecturers;
use App\Models\Students;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Admin',
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'rahasia123',
    ], $attributes));
}

beforeEach(function () {
    RateLimiter::clear('login');
});

it('menolak akses tanpa token dengan JSON 401', function (string $method, string $uri) {
    $this->call($method, $uri)
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
})->with([
    ['GET', '/api/student'],
    ['GET', '/api/courses'],
    ['GET', '/api/user'],
    ['DELETE', '/api/student/01HXXXXXXXXXXXXXXXXXXXXXXX'],
    ['POST', '/api/user/store'],
    ['POST', '/api/user'],
]);

it('tidak lagi mengizinkan pendaftaran akun tanpa login', function () {
    $this->postJson('/api/user/store', [
        'name' => 'Penyusup',
        'username' => 'penyusup',
        'email' => 'x@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(401);

    expect(User::where('username', 'penyusup')->exists())->toBeFalse();
});

it('login berhasil, mengembalikan token dengan masa berlaku, dan tanpa password', function () {
    makeUser();

    $response = $this->postJson('/api/login', ['username' => 'Admin', 'password' => 'rahasia123'])
        ->assertOk()
        ->assertJsonStructure(['token', 'expires_at', 'user' => ['id', 'username']])
        ->assertJsonMissingPath('user.password');

    $token = $response->json('token');
    $this->withToken($token)->getJson('/api/courses')->assertOk();
});

it('login gagal dengan password salah', function () {
    makeUser();

    $this->postJson('/api/login', ['username' => 'admin', 'password' => 'salah'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('username');
});

it('membatasi percobaan login menjadi 5 per menit', function () {
    makeUser();

    foreach (range(1, 5) as $i) {
        $this->postJson('/api/login', ['username' => 'admin', 'password' => 'salah'])->assertStatus(422);
    }

    $this->postJson('/api/login', ['username' => 'admin', 'password' => 'rahasia123'])->assertStatus(429);
});

it('token kedaluwarsa setelah masa berlakunya habis', function () {
    makeUser();
    $token = $this->postJson('/api/login', ['username' => 'admin', 'password' => 'rahasia123'])->json('token');

    $this->travel(config('sanctum.expiration') + 1)->minutes();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/api/courses')->assertStatus(401);
});

it('logout mencabut token', function () {
    makeUser();
    $token = $this->postJson('/api/login', ['username' => 'admin', 'password' => 'rahasia123'])->json('token');

    $this->withToken($token)->postJson('/api/logout')->assertOk();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/api/courses')->assertStatus(401);
});

it('pengguna yang login dapat membuat akun baru dengan password ter-hash', function () {
    Sanctum::actingAs(makeUser());

    $this->postJson('/api/user/store', [
        'name' => 'Operator',
        'username' => 'operator',
        'email' => 'operator@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertCreated()->assertJsonMissingPath('data.password');

    $user = User::where('username', 'operator')->first();
    expect($user)->not->toBeNull()
        ->and(password_verify('password123', $user->password))->toBeTrue();
});

it('tidak bisa menghapus akun sendiri yang sedang login', function () {
    $admin = makeUser();
    Sanctum::actingAs($admin);

    $this->deleteJson("/api/user/{$admin->id}")->assertStatus(422);
    expect(User::find($admin->id))->not->toBeNull();
});

it('menjalankan CRUD mahasiswa dengan validasi unik saat update', function () {
    Sanctum::actingAs(makeUser());

    $a = $this->postJson('/api/student', [
        'name' => 'Budi', 'email' => 'budi@example.com', 'NIM' => '221000001',
        'major' => 'Informatika', 'enrollment_year' => '2022',
    ])->assertCreated()->json('data.student_id');

    $this->postJson('/api/student', [
        'name' => 'Sari', 'email' => 'sari@example.com', 'NIM' => '221000002',
        'major' => 'Informatika', 'enrollment_year' => '2022',
    ])->assertCreated();

    // Update dengan email sendiri tetap boleh, email milik orang lain ditolak
    $this->putJson("/api/student/{$a}", ['email' => 'budi@example.com', 'name' => 'Budi S'])->assertOk();
    $this->putJson("/api/student/{$a}", ['email' => 'sari@example.com'])->assertStatus(422);

    $this->deleteJson("/api/student/{$a}")->assertOk();
    $this->getJson("/api/student/{$a}")->assertNotFound();
});

it('memvalidasi SKS mata kuliah sebagai angka', function () {
    Sanctum::actingAs(makeUser());

    $this->postJson('/api/courses', ['name' => 'Basis Data', 'code' => 'IF201', 'credits' => 'tiga', 'semester' => '3'])
        ->assertStatus(422)->assertJsonValidationErrors('credits');

    $this->postJson('/api/courses', ['name' => 'Basis Data', 'code' => 'IF201', 'credits' => 3, 'semester' => '3'])
        ->assertCreated();
});

it('relasi dosen dan mata kuliah melalui tabel pengampu berfungsi', function () {
    Sanctum::actingAs(makeUser());

    $course = Courses::create(['name' => 'Algoritma', 'code' => 'IF101', 'credits' => 3, 'semester' => '1']);
    $lecturer = Lecturers::create(['name' => 'Dosen A', 'NIP' => '1', 'department' => 'IF', 'email' => 'a@example.com']);

    $this->postJson('/api/courseslecturers', [
        'course_id' => $course->course_id, 'lecturer_id' => $lecturer->lecturer_id, 'role' => 'Main Lecturer',
    ])->assertCreated();

    expect($course->lecturers()->first()->lecturer_id)->toBe($lecturer->lecturer_id)
        ->and($lecturer->courses()->first()->course_id)->toBe($course->course_id);
});

it('enrollment menolak pendaftaran ganda', function () {
    Sanctum::actingAs(makeUser());

    $student = Students::create(['name' => 'Budi', 'email' => 'b@example.com', 'NIM' => '221000001', 'major' => 'IF', 'enrollment_year' => '2022']);
    $course = Courses::create(['name' => 'Algoritma', 'code' => 'IF101', 'credits' => 3, 'semester' => '1']);
    $payload = ['student_id' => $student->student_id, 'course_id' => $course->course_id, 'status' => 'active'];

    $this->postJson('/api/enrollment', $payload)->assertCreated();
    $this->postJson('/api/enrollment', $payload)->assertStatus(409);
});

it('seeder menolak berjalan tanpa SEED_ADMIN_PASSWORD', function () {
    config(['app.seed_admin_password' => null]);
    $this->seed(\Database\Seeders\UserSeeder::class);
})->throws(RuntimeException::class);

it('seeder lengkap berjalan dengan SEED_ADMIN_PASSWORD', function () {
    config(['app.seed_admin_password' => 'rahasiaAdmin1']);
    $this->seed();

    expect(User::where('username', 'admin')->exists())->toBeTrue()
        ->and(Students::count())->toBe(2);

    $this->postJson('/api/login', ['username' => 'admin', 'password' => 'rahasiaAdmin1'])->assertOk();
});
