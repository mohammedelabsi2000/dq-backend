<?php

namespace Database\Factories;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Spatie\Permission\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleNames = [
            'admin', 'manager', 'supervisor', 'teacher', 'student', 'user'
        ];

        return [
            'name' => fake()->unique()->randomElement($roleNames) . '_' . fake()->unique()->randomNumber(4),
            'guard_name' => 'sanctum',
        ];
    }

    /**
     * Create an admin role with all permissions
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'guard_name' => 'sanctum',
        ]);
    }

    /**
     * Create a teacher role
     */
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'teacher',
            'guard_name' => 'sanctum',
        ]);
    }

    /**
     * Create a student role
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'student',
            'guard_name' => 'sanctum',
        ]);
    }
}
