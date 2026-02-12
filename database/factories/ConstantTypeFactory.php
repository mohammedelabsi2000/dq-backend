<?php

namespace Database\Factories;

use App\Models\ConstantType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConstantType>
 */
class ConstantTypeFactory extends Factory
{
    protected $model = ConstantType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->text(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
