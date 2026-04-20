<?php

namespace Database\Factories;

use App\Enums\HalaqaReferenceType;
<<<<<<< 59-testing09-feature-tests-remaining-crud-controllers
use App\Models\Center;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

class HalaqaFactory extends Factory
{
    public function definition(): array
    {
        $center = Center::first();

        $typeId = Constant::whereHas(
            'type',
            fn($q) =>
            $q->where('name', 'halaqa_types')
        )->value('id');

        return [
            'name'           => $this->faker->words(3, true) . ' حلقة',
            'reference_id'   => $center?->id ?? 1,
            'reference_type' => HalaqaReferenceType::Center,
            'type_id'        => $typeId,
=======
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
>>>>>>> main
        ];
    }
}
