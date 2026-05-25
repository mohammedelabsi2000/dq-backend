<?php

namespace Database\Factories;

use App\Enums\PeriodUnit;
use App\Models\Plan;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $period = $this->faker->numberBetween(1, 12);
        return [
            'name' => $this->faker->unique()->sentence(1),
            'description' => $this->faker->paragraph(),
            'period_unit' => $this->faker->randomElement(PeriodUnit::cases())->value,
            'period' => $period,
            'min_period' => $this->faker->optional()->numberBetween(1, $period),
            'max_period' => $this->faker->optional()->numberBetween($period, 24),
            'tolerance' => $this->faker->numberBetween(0, 7),
            'is_active' => $this->faker->boolean(),
        ];
    }
}