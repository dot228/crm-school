<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id'   => User::factory()->student(),
            'enrollment_id'=> null,
            'payment_type' => 'lessons',
            'amount'       => fake()->randomFloat(2, 200, 2000),
            'status'       => 'pending',
            'method'       => 'cash',
            'payment_date' => now()->toDateString(),
        ];
    }

    public function paid(): static  { return $this->state(['status' => 'paid']); }
    public function money(): static { return $this->state(['payment_type' => 'money', 'enrollment_id' => null]); }
}
