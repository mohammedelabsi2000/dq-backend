<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Helpers\ConstantHelper;
use App\Models\Student;
use App\Models\User;
use App\Models\Constant;
use App\Models\Halaqa;
use App\Models\Mosque;
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
        $identity = $this->faker->unique()->numerify('#########');

        $fName = fake()->firstName();
        $sName = fake()->firstNameMale(); // اسم الأب
        $thName = fake()->firstNameMale(); // اسم الجد
        $family = fake()->lastName();

        // الحصول على معرفات عشوائية من جدول constants للأنواع المطلوبة
        $maritalStatusId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('marital_status')
        );
        $moneyStatusId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('money_status')
        );
        $prefixNameId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('prefix_name')
        );
        $guardianTypeId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('guardian_type')
        );


        if (
            Constant::where('name', 'بنفسه')->first()?->id !== $guardianTypeId
        ) { // ولي
            $guardian = User::inRandomOrder()->first() ?? User::factory()->create();
        } else {
            $guardian = User::where('identity', $identity)->first()
                ?? User::factory()->create([
                    'identity' => $identity,
                ]);
        }

        return [
            'identity' => $identity,
            'fName' => $fName,
            'sName' => $sName,
            'thName' => $thName,
            'family' => $family,
            'dob' => fake()->optional(0.8)->dateTimeBetween('-18 years', '-6 years'),
            'mosque_id' => (Mosque::factory()->create()?->id) ?? 1,
            'location' => fake()->optional(0.7)->address(),
            'gender' => fake()->randomElement(Gender::cases())->value,
            'marital_status_id' => $maritalStatusId,
            'money_status_id' => $moneyStatusId,
            'prefix_name_id' => $prefixNameId,
            'guardian_id' => $guardian?->identity,
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
        return $this->state(fn(array $attributes) => [
            'gender' => Gender::Male->code(),
        ]);
    }

    /**
     * Indicate that the student is female.
     */
    public function female(): static
    {
        return $this->state(fn(array $attributes) => [
            'gender' => Gender::Female->code(),
        ]);
    }

    /**
     * Set specific guardian.
     */
    public function withGuardian(User $guardian): static
    {
        return $this->state(fn(array $attributes) => [
            'guardian_id' => $guardian->identity,
        ]);
    }

    /**
     * Set specific mosque.
     */
    public function inMosque(int $mosqueId): static
    {
        return $this->state(fn(array $attributes) => [
            'mosque_id' => $mosqueId,
        ]);
    }

    /**
     * @param int|null $halaqaId
     * @return static
     */
    public function withHalaqa(?int $halaqaId = null): static
    {
        return $this->afterCreating(function (Student $student) use ($halaqaId) {
            $statusId = fake()->randomElement(
                ConstantHelper::getConstantIdsByType('enrollment_status')
            );
            if (!$halaqaId) {
                $halaqa = Halaqa::factory()->create();
                $halaqaId = $halaqa->id;
            }
            $student->halaqas()->attach($halaqaId, [
                'enrollment_status_id' => $statusId,
                'from_date' => now(),
            ]);

        });
    }
}