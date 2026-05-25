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
        // Plan::factory()->count(5)->create();
        Plan::all()->each(function ($plan) {
            $lastOrder = $plan->levels()->max('order') ?? 0;
            Level::factory()
                ->count(rand(4, 10))
                ->sequence(fn($sequence) => [
                    'plan_id' => $plan->id,
                    'order' => $lastOrder + $sequence->index + 1,
                ])
                ->create();
        });
    }
}
