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
        // خطط تحفيظ القرآن
        $hifdhPlan = Plan::create([
            'name' => 'خطة تحفيظ القرآن الكريم - 3 سنوات',
            'type_id' => Constant::whereHas('constantType', fn($q) => $q->where('name', 'plan_type'))->where('name', 'تحفيظ القرآن')->first()->id,
            'target_group_id' => Constant::whereHas('constantType', fn($q) => $q->where('name', 'target_group'))->where('name', 'أطفال')->first()->id,
            'description' => 'خطة متكاملة لتحفيظ القرآن الكريم خلال 3 سنوات',
            'level_numbers' => 3,
            'notes' => 'تتضمن مستويات متدرجة',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $timeUnitDay = Constant::whereHas('constantType', fn($q) => $q->where('name', 'time_unit'))->where('name', 'يوم')->first()->id;
        $timeUnitMonth = Constant::whereHas('constantType', fn($q) => $q->where('name', 'time_unit'))->where('name', 'شهر')->first()->id;

        PlanLevel::create([
            'name' => 'المستوى الأول - جزء عم',
            'plan_id' => $hifdhPlan->id,
            'level_order' => 1,
            'time_of_level' => 12,
            'time_unit_id' => $timeUnitMonth,
            'min_time' => 10,
            'min_time_unit_id' => $timeUnitMonth,
            'max_time' => 14,
            'max_time_unit_id' => $timeUnitMonth,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        PlanLevel::create([
            'name' => 'المستوى الثاني - جزء تبارك',
            'plan_id' => $hifdhPlan->id,
            'level_order' => 2,
            'time_of_level' => 12,
            'time_unit_id' => $timeUnitMonth,
            'min_time' => 10,
            'min_time_unit_id' => $timeUnitMonth,
            'max_time' => 14,
            'max_time_unit_id' => $timeUnitMonth,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        PlanLevel::create([
            'name' => 'المستوى الثالث - بقية الأجزاء',
            'plan_id' => $hifdhPlan->id,
            'level_order' => 3,
            'time_of_level' => 12,
            'time_unit_id' => $timeUnitMonth,
            'min_time' => 10,
            'min_time_unit_id' => $timeUnitMonth,
            'max_time' => 16,
            'max_time_unit_id' => $timeUnitMonth,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // خطط إضافية
        Plan::factory(10)->create()->each(function ($plan) {
            PlanLevel::factory()
                ->count(fake()->numberBetween(3, 8))
                ->create([
                    'plan_id' => $plan->id,
                    'level_order' => function () {
                        static $order = 1;
                        return $order++;
                    },
                ]);
        });
    }
}