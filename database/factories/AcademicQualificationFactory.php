<?php

namespace Database\Factories;

use App\Models\AcademicQualification;
use App\Models\Constant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicQualificationFactory extends Factory
{
    protected $model = AcademicQualification::class;

    public function definition(): array
    {
        return [
            'academic_degree_id' => Constant::where('constant_type_id', function ($q) {
                $q->select('id')->from('constant_types')->where('name', 'academic_degree');
            })->inRandomOrder()->first()->id ?? Constant::factory(),
            'major_id' => Constant::where('constant_type_id', function ($q) {
                $q->select('id')->from('constant_types')->where('name', 'major');
            })->inRandomOrder()->first()->id ?? Constant::factory(),
            'person_type' => 'App\\Models\\User',
            'person_id' => User::factory(),
            'detail' => $this->faker->optional()->sentence(),
            'date_graduate' => $this->faker->optional()->year(), // يعطي سنة مثل 2024
            'certificate_link' => $this->faker->optional()->url(),
            'educational_institution' => $this->faker->company(),
            'notes' => $this->faker->optional()->text(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
