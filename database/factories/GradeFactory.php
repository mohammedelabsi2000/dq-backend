<?php

namespace Database\Factories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        $rangeStart = $this->faker->numberBetween(1, 50);
        $rangeEnd = $rangeStart + $this->faker->numberBetween(10, 30);
        
        return [
            'name' => 'Grade ' . $this->faker->unique()->numberBetween(1, 12),
            'DQ_range_from' => (string)$rangeStart,
            'DQ_range_to' => (string)$rangeEnd,
        ];
    }
}