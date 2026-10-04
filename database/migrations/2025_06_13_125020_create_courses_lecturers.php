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
        Schema::create('courses_lecturers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('course_id');
            $table->ulid('lecturer_id');
            $table->string('role');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('course_id')
                  ->references('course_id')
                  ->on('courses')
                  ->onDelete('cascade');
                  
            $table->foreign('lecturer_id')
                  ->references('lecturer_id')
                  ->on('lecturers')
                  ->onDelete('cascade');

            // Composite unique constraint
            $table->unique(['course_id', 'lecturer_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses_lecturers');
    }
};