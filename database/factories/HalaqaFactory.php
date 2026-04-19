<?php

namespace Database\Factories;

use App\Enums\HalaqaReferenceType;
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
        ];
    }
}
