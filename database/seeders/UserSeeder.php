<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mosque;
use App\Models\AcademicQualification;
use App\Models\PersonalCourse;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مستخدم مسؤول
        User::create([
            'fName' => 'Admin',
            'sName' => 'System',
            'thName' => '',
            'family' => 'Administrator',
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'gender' => 'male',
            'phone' => '0555555555',
            'identity' => '1000000001',
            'mosque_id' => Mosque::first()->id ?? null,
            // 'created_by' => 1,
            // 'updated_by' => 1,
        ]);

        // إنشاء 50 مستخدم عادي
        User::factory(50)
            ->create()
            ->each(function ($user) {
                // لكل مستخدم 1-3 مؤهلات أكاديمية
                AcademicQualification::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->create([
                        'person_id' => $user->id,
                        'person_type' => 'App\\Models\\User',
                    ]);

                // لكل مستخدم 0-5 دورات
                PersonalCourse::factory()
                    ->count(fake()->numberBetween(0, 5))
                    ->create([
                        'person_id' => $user->id,
                        'person_type' => 'App\\Models\\User',
                    ]);
            });
    }
}