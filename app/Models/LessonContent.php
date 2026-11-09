<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    const TYPES = ['text', 'video', 'file', 'link'];

    protected $fillable = [
        'lesson_id', 'type', 'title', 'body',
        'url', 'file_path', 'order', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order'        => 'integer',
    ];

    public function lesson() { return $this->belongsTo(Lesson::class); }
}
