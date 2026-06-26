<?php

namespace Database\Factories;

use App\Models\Investor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Investor> */
class InvestorFactory extends Factory
{
    protected $model = Investor::class;

    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->name(),
            'age' => fake()->numberBetween(18, 90),
        ];
    }
}
