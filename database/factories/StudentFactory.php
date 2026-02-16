<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fName = fake()->firstName();
        $sName = fake()->firstNameMale(); // اسم الأب
        $thName = fake()->firstNameMale(); // اسم الجد
        $family = fake()->lastName();
        
        // الحصول على معرفات عشوائية من جدول constants للأنواع المطلوبة
        $maritalStatusId = Constant::where('constant_type_id', 'marital_status')->inRandomOrder()->first()?->id;
        $moneyStatusId = Constant::where('constant_type_id', 'money_status')->inRandomOrder()->first()?->id;
        $prefixNameId = Constant::where('constant_type_id', 'name_prefix')->inRandomOrder()->first()?->id;
        $guardianTypeId = Constant::where('constant_type_id', 'guardian_relation')->inRandomOrder()->first()?->id;
        
        // الحصول على مستخدم عشوائي ليكون ولي الأمر
        $guardian = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'fName' => $fName,
            'sName' => $sName,
            'thName' => $thName,
            'family' => $family,
            'dob' => fake()->optional(0.8)->dateTimeBetween('-18 years', '-6 years'),
            'mosque_id' => \App\Models\Mosque::inRandomOrder()->first()?->id ?? 1,
            'location' => fake()->optional(0.7)->address(),
            'gender' => fake()->randomElement(['male', 'female']),
            'marital_status_id' => $maritalStatusId,
            'money_status_id' => $moneyStatusId,
            'prefix_name_id' => $prefixNameId,
            'guardian_id' => $guardian->identity,
            'guardian_type_id' => $guardianTypeId,
            'phone' => fake()->optional(0.6)->phoneNumber(),
            'whatsapp' => fake()->optional(0.4)->phoneNumber(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Student $student) {
            // يمكن إضافة منطق بعد الإنشاء
        })->afterCreating(function (Student $student) {
            // يمكن إضافة منطق بعد الحفظ
        });
    }

    /**
     * Indicate that the student is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'male',
        ]);
    }

    /**
     * Indicate that the student is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'female',
        ]);
    }

    /**
     * Set specific guardian.
     */
    public function withGuardian(User $guardian): static
    {
        return $this->state(fn (array $attributes) => [
            'guardian_id' => $guardian->identity,
        ]);
    }

    /**
     * Set specific mosque.
     */
    public function inMosque(int $mosqueId): static
    {
        return $this->state(fn (array $attributes) => [
            'mosque_id' => $mosqueId,
        ]);
    }
}