<?php

namespace Database\Factories;

use App\Helpers\ConstantHelper;
use App\Models\Halaqa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HalaqaStatus>
 */
class HalaqaStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $fromDate = $this->faker->optional()->date();

        return [
            'halaqa_id' => Halaqa::factory(),
            'sponsorship_type_id' => fake()->randomElement(
                ConstantHelper::getConstantIdsByType('sponsorship_type')
            ),
            'from_date' => $fromDate,
            'to_date' => $this->faker->optional()->date('Y-m-d', "{$fromDate} +1 year"),
            'notes' => fake()->optional()->text(),
        ];
    }
}
