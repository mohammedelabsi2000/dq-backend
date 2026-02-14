<?php

namespace Database\Factories;

use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConstantFactory extends Factory
{
    protected $model = Constant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'constant_type_id' => ConstantType::factory(),
            'parent_id' => null,
            'is_active' => $this->faker->boolean(90),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function withParent(): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => Constant::inRandomOrder()->first()->id ?? Constant::factory(),
        ]);
    }
}