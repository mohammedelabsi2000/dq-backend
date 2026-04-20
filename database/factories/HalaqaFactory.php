<?php

namespace Database\Factories;

use App\Enums\HalaqaReferenceType;
use App\Helpers\ConstantHelper;
use App\Models\Center;
use App\Models\ConstantType;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Halaqa>
 */
class HalaqaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $referenceType = $this->faker->randomElement(HalaqaReferenceType::cases());

        // Get reference ID based on type
        $modelClass = $referenceType->model();
        $referenceId = $modelClass
            ? $modelClass::inRandomOrder()->value('id')
            : null;

        if (!$referenceId) {
            $referenceId = $modelClass::factory()->create()->id;
        }

        $typeId = $this->faker->randomElement(
            ConstantHelper::getConstantIdsByType('halaqa_types')
        );
        
        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->optional()->address(),
            'description' => $this->faker->optional()->text(),
            'reference_type' => $referenceType->code(),
            'reference_id' => $referenceId,
            'type_id' => $typeId,
        ];
    }
}
