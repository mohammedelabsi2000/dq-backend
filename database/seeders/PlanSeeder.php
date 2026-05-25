<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\PlanLevel;
use App\Models\Constant;
use App\Models\Level;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::factory()
            ->count(5)
            ->create();

        Level::factory()
            ->count(30)
            ->create();
    }
}
