<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\PlanLevel;
use App\Models\Constant;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Create plans and levels
        // Plan::factory()->count(5)->create();
        /* Plan::all()->each(function ($plan) {
            $lastOrder = $plan->levels()->max('order') ?? 0;
            Level::factory()
                ->count(rand(4, 10))
                ->sequence(fn($sequence) => [
                    'plan_id' => $plan->id,
                    'order' => $lastOrder + $sequence->index + 1,
                ])
                ->create();
        }); */

        // Create tracks and subjects
        // Track::factory()->count(5)->create();
        Track::factory()->create(['name' => 'الحفظ والتثبيت',]);
        Track::factory()->create(['name' => 'المعرفي',]);
        Track::factory()->create(['name' => 'القيمي',]);
        
        Track::all()->each(function ($track) {
            Subject::factory()
                ->count(rand(3, 7))
                ->create(['track_id' => $track->id]);
        });
    }
}
