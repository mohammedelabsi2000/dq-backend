<?php

namespace Database\Factories;

use App\Models\PlanLevel;
use App\Models\Plan;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanLevelFactory extends Factory
{
    protected $model = PlanLevel::class;

    public function definition(): array
    {
        $timeUnitId = Constant::where('constant_type_id', function ($q) {
            $q->select('id')->from('constant_types')->where('name', 'time_unit');
        })->inRandomOrder()->first()->id ?? Constant::factory();

        return [
            'name' => 'Level ' . $this->faker->word(),
            'plan_id' => Plan::factory(),
            'time_of_level' => $this->faker->optional()->numberBetween(30, 180),
            'time_unit_id' => $timeUnitId,
            'max_time' => $this->faker->optional()->numberBetween(180, 360),
            'max_time_unit_id' => $timeUnitId,
            'min_time' => $this->faker->optional()->numberBetween(20, 60),
            'min_time_unit_id' => $timeUnitId,
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
