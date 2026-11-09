<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeworkSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id'      => User::factory()->student(),
            'lesson_id'       => Lesson::factory(),
            'status'          => 'pending',
            'assignment_text' => fake()->sentence(),
            'assigned_at'     => now(),
        ];
    }

    public function submitted(): static { return $this->state(['status' => 'submitted', 'content' => fake()->paragraph(), 'submitted_at' => now()]); }
    public function reviewed(): static  { return $this->state(['status' => 'reviewed', 'grade' => fake()->numberBetween(1, 12)]); }
}
