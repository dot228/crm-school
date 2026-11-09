<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    const STATUSES = ['draft', 'scheduled', 'completed', 'cancelled', 'no_show'];

    const ABSENCE_TYPES = ['excused', 'unexcused'];

    protected $fillable = [
        'lesson_id', 'student_id', 'teacher_id', 'substitute_teacher_id',
        'scheduled_date', 'scheduled_time', 'duration_minutes',
        'status', 'absence_type', 'absence_reason',
        'zoom_link', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function lesson()             { return $this->belongsTo(Lesson::class); }
    public function student()            { return $this->belongsTo(User::class, 'student_id'); }
    public function teacher()            { return $this->belongsTo(User::class, 'teacher_id'); }
    public function substituteTeacher()  { return $this->belongsTo(User::class, 'substitute_teacher_id'); }

    public function getEffectiveTeacherAttribute(): \App\Models\User
    {
        return $this->substituteTeacher ?? $this->teacher;
    }

    public function shouldDeductBalance(): bool
    {
        return $this->status === 'completed'
            || ($this->status === 'no_show' && $this->absence_type === 'unexcused');
    }
}
