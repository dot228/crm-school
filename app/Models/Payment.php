<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const STATUSES = ['pending', 'paid', 'cancelled', 'refunded'];
    const METHODS  = ['cash', 'card', 'bank_transfer', 'online'];

    protected $fillable = [
        'student_id', 'enrollment_id', 'tariff_plan_id',
        'amount', 'lessons_count', 'status', 'method', 'payment_date', 'notes',
        'payment_type',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function tariffPlan()
    {
        return $this->belongsTo(TariffPlan::class);
    }
}
