<?php

namespace Database\Factories;

use App\Models\Constant;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HalaqaStudent>
 */
class HalaqaStudentFactory extends Factory
{
    protected $model = HalaqaStudent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromDate = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'halaqa_id' => Halaqa::factory(),
            'student_id' => Student::factory(),
            'from_date' => $fromDate,
            'to_date' => $this->faker->optional(0.3)->dateTimeBetween($fromDate, '+1 year'),
            'status_id' => Constant::where('type', 'enrollment_status')->inRandomOrder()->first() ?? Constant::factory(),
        ];
    }

    /**
     * Indicate that the enrollment is currently active.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'to_date' => null,
            ];
        });
    }

    /**
     * Indicate that the enrollment has ended.
     */
    public function ended()
    {
        return $this->state(function (array $attributes) {
            return [
                'to_date' => now()->subDays(rand(1, 30)),
            ];
        });
    }
}
