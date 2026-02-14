<?php

namespace Database\Factories;

use App\Models\PersonalCourse;
use App\Models\Constant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalCourseFactory extends Factory
{
    protected $model = PersonalCourse::class;

    public function definition(): array
    {
        return [
            'course_name' => $this->faker->sentence(3),
            'notes' => $this->faker->optional()->paragraph(),
            'hours' => $this->faker->optional()->numberBetween(10, 200),
            'provider' => $this->faker->company(),
            'place' => $this->faker->city(),
            'certificate_link' => $this->faker->optional()->url(),
            'type_id' => Constant::where('constant_type_id', function($q) {
                $q->select('id')->from('constant_types')->where('name', 'course_type');
            })->inRandomOrder()->first()->id ?? Constant::factory(),
            'person_type' => 'App\\Models\\User',
            'person_id' => User::factory(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}