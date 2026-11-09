<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    const STATUSES = ['scheduled', 'completed', 'cancelled'];

    protected $fillable = [
        'title', 'description', 'course_id', 'group_id',
        'order', 'scheduled_at', 'duration_minutes',
        'status', 'zoom_link', 'homework',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function contents()
    {
        return $this->hasMany(LessonContent::class)->orderBy('order');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
