<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\PlanLevel;
use App\Models\Constant;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::factory()
            ->count(5)
            ->create();
    }
}