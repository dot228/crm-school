<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id'        => Lesson::factory(),
            'student_id'       => User::factory()->student(),
            'teacher_id'       => User::factory()->teacher(),
            'scheduled_date'   => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'scheduled_time'   => '10:00:00',
            'duration_minutes' => 60,
            'status'           => 'scheduled',
        ];
    }

    public function draft():     static { return $this->state(['status' => 'draft']); }
    public function completed(): static { return $this->state(['status' => 'completed']); }
    public function cancelled(): static { return $this->state(['status' => 'cancelled']); }
}
