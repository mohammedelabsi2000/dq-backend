<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $plan = \App\Models\Plan::inRandomOrder()->first();

        return [
            'plan_id' => $plan ? $plan->id : null,
            'name' => $this->faker->sentence(2),
            'order' => $this->faker->numberBetween(1, $plan->levels()->count() + 1),
            'period_unit' => $this->faker->randomElement(\App\Enums\PeriodUnit::cases())->value,
            'period' => $this->faker->numberBetween(1, 12),
            'min_period' => $this->faker->optional()->numberBetween(1, 6),
            'max_period' => $this->faker->optional()->numberBetween(6, 24),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
