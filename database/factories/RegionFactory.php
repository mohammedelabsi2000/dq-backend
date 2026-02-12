<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'branch_id' => Branch::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}