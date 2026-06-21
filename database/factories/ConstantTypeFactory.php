<?php

namespace Database\Factories;

use App\Models\ConstantType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConstantTypeFactory extends Factory
{
    protected $model = ConstantType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->text(200),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}