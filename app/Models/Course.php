<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    const LEVELS = ['beginner', 'intermediate', 'advanced'];
    const STATUSES = ['active', 'inactive', 'archived'];

    protected $fillable = [
        'lesson_price',
        'title', 'description', 'price', 'duration_hours',
        'level', 'status', 'image', 'teacher_id',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'lesson_price' => 'decimal:2',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://placehold.co/600x400/6366f1/ffffff?text=' . urlencode($this->title);
    }

    public function getLevelLabelAttribute(): string
    {
        return ucfirst($this->level);
    }

    public function getStudentsCountAttribute(): int
    {
        return Enrollment::whereHas('group', fn($q) => $q->where('course_id', $this->id))->count();
    }
}
