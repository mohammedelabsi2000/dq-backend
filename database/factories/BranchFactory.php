<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Branch',
            'notes' => $this->faker->optional()->sentence(),
            'max_replacement_limit' => $this->faker->numberBetween(10, 100),
            'min_replacement_limit' => $this->faker->numberBetween(1, 9),
        ];
    }
}