<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id'      => User::factory()->student(),
            'group_id'        => Group::factory(),
            'status'          => 'active',
            'enrolled_at'     => now()->toDateString(),
            'lessons_balance' => 0,
        ];
    }
}
