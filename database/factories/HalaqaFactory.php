<?php

namespace Database\Factories;

use App\Enums\HalaqaReferenceType;
use App\Helpers\ConstantHelper;
use App\Models\Halaqa;
use App\Models\HalaqaStatus;
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

        $fromDate = $this->faker->optional()->date();

        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->optional()->address(),
            'description' => $this->faker->optional()->text(),
            'reference_type' => $referenceType->code(),
            'reference_id' => $referenceId,
            'type_id' => $typeId,
            'from_date' => $fromDate,
            'to_date' => $fromDate
                ? $this->faker->optional()->dateTimeBetween($fromDate, '+1 year')?->format('Y-m-d')
                : $this->faker->optional()->date(),
        ];
    }


    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Halaqa $halaqa) {
            // يمكن إضافة منطق بعد الإنشاء
        })->afterCreating(function (Halaqa $halaqa) {
            /* HalaqaStatus::factory()->create([
                'halaqa_id' => $halaqa->id,
            ]); */
        });
    }

    /**
     * Randomly generate statuses for the halaqa
     * 
     * @param int $count
     * @return static
     */
    public function withStatuses(?int $count = 1): static
    {
        return $this->afterCreating(function ($halaqa) use ($count) {
            HalaqaStatus::factory($count)->create([
                'halaqa_id' => $halaqa->id,
            ]);
        });
    }
}
