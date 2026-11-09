<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => 'Group ' . fake()->word(),
            'course_id'    => Course::factory(),
            'teacher_id'   => User::factory()->teacher(),
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addMonths(3)->toDateString(),
            'max_students' => 10,
            'status'       => 'active',
        ];
    }
}
