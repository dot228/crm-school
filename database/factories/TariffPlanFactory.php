<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TariffPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->words(2, true),
            'count_lessons' => fake()->numberBetween(4, 20),
            'amount'        => fake()->randomFloat(2, 500, 3000),
            'status'        => 'active',
        ];
    }
}
