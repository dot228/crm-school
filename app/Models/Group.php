<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    const STATUSES = ['active', 'completed', 'cancelled'];

    protected $fillable = [
        'name', 'course_id', 'teacher_id',
        'start_date', 'end_date', 'max_students',
        'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'group_id', 'student_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function getStudentsCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getSpotsLeftAttribute(): int
    {
        return max(0, $this->max_students - $this->students_count);
    }
}
