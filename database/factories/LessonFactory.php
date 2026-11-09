<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'            => 'Lesson ' . fake()->word(),
            'description'      => fake()->sentence(),
            'course_id'        => Course::factory(),
            'group_id'         => null,
            'order'            => fake()->numberBetween(1, 20),
            'duration_minutes' => 60,
            'status'           => 'scheduled',
        ];
    }
}
