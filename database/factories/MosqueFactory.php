<?php

namespace Database\Factories;

use App\Models\Mosque;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class MosqueFactory extends Factory
{
    protected $model = Mosque::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Mosque',
            'notes' => $this->faker->optional()->sentence(),
            'region_id' => Region::factory(),
        ];
    }
}