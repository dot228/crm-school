<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffPlan extends Model
{
    use HasFactory;
    const STATUSES = ['active', 'inactive'];

    protected $fillable = ['name', 'description', 'amount', 'count_lessons', 'status'];
}
