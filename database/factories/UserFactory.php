<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Helpers\ConstantHelper;
use App\Models\Constant;
use App\Models\Mosque;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fName = $this->faker->firstName();
        $sName = $this->faker->firstName();
        $thName = $this->faker->firstName();
        $family = $this->faker->lastName();

        return [
            'fName' => $fName,
            'sName' => $sName,
            'thName' => $thName,
            'family' => $family,
            'name' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'dob' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'mosque_id' => Mosque::inRandomOrder()->first()->id ?? null,
            'location' => $this->faker->address(),
            'gender' => fake()->randomElement(Gender::cases())->value,
            'marital_status_id' => fake()->randomElement(
                ConstantHelper::getConstantIdsByType('marital_status')
            ),
            'numChildren' => $this->faker->numberBetween(0, 8),
            'identity' => $this->faker->unique()->numerify('#########'),
            'phone' => $this->faker->unique()->phoneNumber(),
            'whatsapp' => $this->faker->optional()->phoneNumber(),
            'jobname' => $this->faker->jobTitle(),
            'job_place' => $this->faker->company(),
            'job_salary' => $this->faker->optional()->randomFloat(2, 1000, 50000),
            'prefix_name_id' => Constant::where('constant_type_id', function ($q) {
                $q->select('id')->from('constant_types')->where('name', 'prefix_name');
            })->inRandomOrder()->first()->id ?? null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
