<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_ADMIN   = 1;
    const ROLE_MANAGER = 2;
    const ROLE_TEACHER = 3;
    const ROLE_STUDENT = 4;

    const ROLES = [
        self::ROLE_ADMIN   => 'Admin',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_TEACHER => 'Teacher',
        self::ROLE_STUDENT => 'Student',
    ];

    const STATUSES = ['active', 'inactive', 'blocked'];

    protected $fillable = [
        'name', 'email', 'password', 'role_id',
        'phone', 'avatar', 'status', 'bio',
        'date_of_birth',
        'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_relation',
        'money_balance',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'          => 'datetime',
        'two_factor_confirmed_at'    => 'datetime',
        'date_of_birth'              => 'date',
        'money_balance'              => 'decimal:2',
    ];

    public function isAdmin(): bool    { return $this->role_id === self::ROLE_ADMIN; }
    public function isManager(): bool  { return $this->role_id === self::ROLE_MANAGER; }
    public function isTeacher(): bool  { return $this->role_id === self::ROLE_TEACHER; }
    public function isStudent(): bool  { return $this->role_id === self::ROLE_STUDENT; }
    public function hasRole(int $role): bool { return $this->role_id === $role; }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role_id] ?? 'Unknown';
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function teachingCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function teachingGroups()
    {
        return $this->hasMany(Group::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'enrollments', 'student_id', 'group_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function availabilities()
    {
        return $this->hasMany(TeacherAvailability::class, 'teacher_id')->orderBy('day_of_week');
    }

    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class, 'student_id');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }

    public function isAdult(): bool
    {
        return $this->age === null || $this->age >= 18;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function twoFactorRecoveryCodesArray(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }
        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }
}
