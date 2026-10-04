<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('student_id')->primary();
            $table->string('name');
            $table->string('email');
            $table->char('NIM', 9);
            $table->string('major');
            // Change enrollment_year to string to store only the year
            $table->string('enrollment_year', 4); // 4 characters for year (e.g., '2022')
            $table->timestamps();
            
            $table->unique('email');
            $table->unique('NIM');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
