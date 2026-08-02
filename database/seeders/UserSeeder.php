<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mosque;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User::where('email', '')->delete();
        // إنشاء مستخدم مسؤول
        User::updateOrCreate([
            'email' => 'admin@tahfiz.dq',
            'identity' => '999999999',
        ], [
            'fName' => 'مسؤول',
            'sName' => '',
            'thName' => '',
            'family' => 'النظام',
            'name' => 'مسؤول النظام ',
            'password' => Hash::make('AdminAdmin'),
            'email_verified_at' => now(),
            'gender' => 'ذكر',
            'phone' => '',
            'mosque_id' => Mosque::first()->id ?? null,
            'is_approved' => '1',
            'is_active' => '1',
        ]);
    }
}
