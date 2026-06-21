<?php

namespace Database\Factories;

use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\User;
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
            'is_active' => true,
            'notes' => $this->faker->optional()->text(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function withParent(): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => Constant::inRandomOrder()->first()->id ?? Constant::factory(),
        ]);
    }
}