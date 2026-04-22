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
        User::firstOrCreate([
            'email' => 'admin@tahfeez.dq',
            'identity' => '100000000',
        ], [
            'fName' => 'رائد',
            'sName' => 'علي',
            'thName' => 'حسن',
            'family' => 'المدهون',
            'name' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'gender' => 'ذكر',
            'phone' => '0555555555',
            'mosque_id' => Mosque::first()->id ?? null,
        ]);
    }
}
