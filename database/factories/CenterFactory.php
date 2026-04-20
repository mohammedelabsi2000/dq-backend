<?php

namespace Database\Factories;

use App\Models\Center;
use App\Models\Mosque;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class CenterFactory extends Factory
{
    protected $model = Center::class;

    public function definition(): array
    {
        $regionId = Region::inRandomOrder()->value('id');

        return [
            'name' => $this->faker->company() . ' Center',
            'notes' => $this->faker->optional()->sentence(),
            'region_id' => $regionId,
            'mosque_id' => Mosque::factory(),
        ];
    }
}