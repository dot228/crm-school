<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;
    const STATUSES = ['pending', 'submitted', 'reviewed'];

    const GRADES = ['A', 'B', 'C', 'D', 'F'];

    protected $fillable = [
        'lesson_id', 'student_id', 'schedule_id',
        'assignment_text', 'assignment_file_path', 'assigned_at',
        'content', 'file_path', 'status',
        'grade', 'teacher_comment', 'teacher_file_path',
        'submitted_at', 'reviewed_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
