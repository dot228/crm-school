<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    const STATUSES = ['active', 'completed', 'cancelled', 'suspended'];

    protected $fillable = [
        'student_id', 'group_id', 'status', 'enrolled_at', 'notes', 'lessons_balance',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function hasLessonsLeft(): bool
    {
        return $this->lessons_balance > 0;
    }
}
