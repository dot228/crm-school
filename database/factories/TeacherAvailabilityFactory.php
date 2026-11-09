<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherAvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id'  => User::factory()->teacher(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'start_time'  => '09:00:00',
            'end_time'    => '17:00:00',
            'is_active'   => true,
        ];
    }
}
