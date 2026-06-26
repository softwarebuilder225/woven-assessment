<?php

namespace Database\Factories;

use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Investment> */
class InvestmentFactory extends Factory
{
    protected $model = Investment::class;

    public function definition(): array
    {
        return [
            'investor_id' => Investor::factory(),
            'amount' => fake()->randomFloat(2, 1000, 1000000),
            'investment_date' => fake()->date(),
        ];
    }
}
