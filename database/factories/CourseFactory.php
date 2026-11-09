<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'        => fake()->words(3, true),
            'description'  => fake()->sentence(),
            'teacher_id'   => User::factory()->teacher(),
            'status'       => 'active',
            'lesson_price' => fake()->randomFloat(2, 100, 500),
        ];
    }
}
