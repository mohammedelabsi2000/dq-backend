<?php

namespace Database\Factories;

use App\Models\Center;
use App\Models\Mosque;
use Illuminate\Database\Eloquent\Factories\Factory;

class CenterFactory extends Factory
{
    protected $model = Center::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Center',
            'notes' => $this->faker->optional()->sentence(),
            'mosque_id' => Mosque::factory(),
        ];
    }
}