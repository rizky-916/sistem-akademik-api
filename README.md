# Sistem Akademik API

**REST API sistem informasi akademik** berbasis **Laravel 12** dan **Laravel Sanctum** untuk mengelola data mahasiswa, dosen, mata kuliah, dosen pengampu, dan pendaftaran mata kuliah (KRS/*enrollment*).

Proyek ini awalnya dikerjakan sebagai tugas UTS mata kuliah PBKK, lalu difinalisasi untuk portofolio dengan fokus pada keamanan, validasi data, dan pengujian otomatis.

---

## Fitur

- **Autentikasi token (Sanctum)**: login, logout, token kedaluwarsa otomatis (default 24 jam).
- **CRUD lengkap** untuk pengguna, mahasiswa, mata kuliah, dosen, dosen pengampu, dan enrollment.
- **Validasi data**: NIM 9 karakter, email dan kode unik, SKS berupa angka, status enrollment terbatas (`active`, `completed`, `dropped`).
- **Pencegahan data ganda**: mahasiswa tidak bisa mengambil mata kuliah yang sama dua kali; kombinasi dosen–mata kuliah–peran tidak boleh duplikat.
- **ID berbasis ULID** untuk semua entitas, bukan angka berurutan (1, 2, 3, ...) yang mudah ditebak.
- **Respons JSON konsisten**, termasuk untuk error 401, 404, 409, 422, dan 429.
- **Tes otomatis** (Pest) untuk alur keamanan dan fitur utama.

## Struktur Data

```
users                     students                courses
─────                     ────────                ───────
id (ULID)                 student_id (ULID)       course_id (ULID)
name, username, email     name, email, NIM        name, code, credits
password (hash)           major, enrollment_year  semester
        │                        │                     │      │
        │                        └──── enrollments ────┘      │
        │                              enrollment_id          │
   personal_access_tokens              status, grade,         │
   (token login Sanctum)               attendance             │
                                                              │
                           lecturers ── courses_lecturers ────┘
                           lecturer_id   id, role
                           name, NIP, department, email
```

## Teknologi

- PHP 8.2+ dan [Laravel 12](https://laravel.com/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) untuk autentikasi token
- MySQL/MariaDB (SQLite untuk pengujian)
- [Pest](https://pestphp.com/) untuk pengujian otomatis

## Menjalankan Secara Lokal

**Prasyarat:** PHP 8.2+, Composer, dan MySQL/MariaDB (misalnya dari XAMPP/Laragon).

```bash
git clone https://github.com/rizky-916/sistem-akademik-api.git
cd sistem-akademik-api

composer install
cp .env.example .env          # Windows CMD: copy .env.example .env
php artisan key:generate
```

Buka file `.env`, lalu sesuaikan:

```env
DB_DATABASE=sistem_akademik
DB_USERNAME=root
DB_PASSWORD=

SEED_ADMIN_PASSWORD=isi-password-admin-yang-kuat   # minimal 8 karakter
CORS_ALLOWED_ORIGINS=http://localhost:3000          # URL frontend
SANCTUM_EXPIRATION=1440                             # masa berlaku token (menit)
```

Buat database `sistem_akademik`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan serve
```

API tersedia di `http://127.0.0.1:8000/api`. Akun awal: username **`admin`** dengan password sesuai `SEED_ADMIN_PASSWORD`.

> Agar token kedaluwarsa ikut dibersihkan dari database, jalankan `php artisan schedule:work` (lokal) atau pasang cron `schedule:run` di server.

## Dokumentasi Endpoint

Semua endpoint selain `POST /api/login` **wajib** menyertakan header:

```
Authorization: Bearer <token>
Accept: application/json
```

### Autentikasi

| Method | Endpoint | Body | Keterangan |
|---|---|---|---|
| POST | `/api/login` | `username`, `password` | Mengembalikan `token`, `expires_at`, dan data `user`. Maks. 5 percobaan/menit per IP. |
| POST | `/api/logout` | – | Mencabut token yang sedang dipakai. |

### Resource

Setiap resource mendukung `GET` (daftar), `POST` (tambah), `GET /{id}` (detail), `PUT/PATCH /{id}` (ubah), dan `DELETE /{id}` (hapus).

| Resource | Endpoint | Field |
|---|---|---|
| Pengguna | `/api/user` (juga `POST /api/user/store`) | `name`, `username`, `email`, `password`, `password_confirmation` |
| Mahasiswa | `/api/student` | `name`, `email`, `NIM` (9 karakter), `major`, `enrollment_year` (4 digit) |
| Mata kuliah | `/api/courses` | `name`, `code`, `credits` (angka 1–24), `semester` |
| Dosen | `/api/lecturers` | `name`, `NIP`, `department`, `email` |
| Dosen pengampu | `/api/courseslecturers` | `course_id`, `lecturer_id`, `role` |
| Enrollment (KRS) | `/api/enrollment` | `student_id`, `course_id`, `status`, `grade`, `attendance` |

### Contoh

```bash
# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"<password-admin>"}'

# Ambil daftar mahasiswa
curl http://127.0.0.1:8000/api/student \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

## Pengujian

```bash
php artisan test
```

Tes memakai database SQLite di memori, jadi tidak mengubah database MySQL Anda. Cakupannya meliputi: akses tanpa token ditolak, pendaftaran akun wajib login, batas percobaan login, token kedaluwarsa, logout, hashing password, CRUD dan validasi unik, relasi dosen–mata kuliah, enrollment ganda, serta seeder.

## Keamanan

- Semua endpoint data hanya bisa diakses dengan token; **pembuatan akun baru wajib login**.
- **Rate limiting**: login 5 percobaan/menit per IP, endpoint lain 60 request/menit per pengguna.
- **Token Sanctum kedaluwarsa** (default 24 jam) dan dicabut saat logout; token pengguna yang dihapus ikut dicabut.
- Password di-hash (bcrypt) dan tidak pernah dikirim dalam respons.
- Pengguna tidak dapat menghapus akunnya sendiri yang sedang dipakai.
- Validasi unik memakai `Rule::unique()->ignore()`, bukan penggabungan string dari input URL.
- **Password admin seeder diambil dari `.env`**, tidak ditulis di kode.
- **CORS** dibatasi ke origin frontend yang terdaftar di `CORS_ALLOWED_ORIGINS`.
- File `.env` tidak ikut di-commit (lihat `.gitignore`).

> Untuk produksi: set `APP_ENV=production` dan `APP_DEBUG=false`, gunakan HTTPS, dan jalankan `composer update` secara berkala untuk pembaruan keamanan dependensi.

## Lisensi

Dirilis di bawah [Lisensi MIT](LICENSE).

---

Dibuat oleh **Rizky Maulana Hafis** — S1 Teknik Informatika, Universitas Muhammadiyah Pontianak.
