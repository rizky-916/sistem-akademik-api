<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus token Sanctum yang sudah kedaluwarsa setiap hari (butuh `php artisan schedule:work` / cron)
Schedule::command('sanctum:prune-expired --hours=24')->daily();
