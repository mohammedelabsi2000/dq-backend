<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(1),
            'type_id' => Constant::where('constant_type_id', function($q) {
                $q->select('id')->from('constant_types')->where('name', 'plan_type');
            })->inRandomOrder()->first()->id ?? Constant::factory(),
            'description' => $this->faker->paragraph(),
            'target_group_id' => Constant::where('constant_type_id', function($q) {
                $q->select('id')->from('constant_types')->where('name', 'target_group');
            })->inRandomOrder()->first()->id ?? Constant::factory(),
            'level_numbers' => $this->faker->numberBetween(3, 10),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}