<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursesLecturers extends Model
{
    use HasUlids;
    
    protected $table = 'courses_lecturers';
    
    protected $fillable = [
        'course_id',
        'lecturer_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'string',
            'lecturer_id' => 'string',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturers::class, 'lecturer_id');
    }
}