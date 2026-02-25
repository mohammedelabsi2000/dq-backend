<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Mosque;
use App\Models\Center;

class BranchRegionMosqueSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء 5 فروع
        $branches = Branch::factory(5)
            ->sequence(
                ['name' => 'الفرع الرئيسي - الرياض'],
                ['name' => 'الفرع الشمالي - الرياض'],
                ['name' => 'فرع جدة'],
                ['name' => 'فرع الدمام'],
                ['name' => 'فرع مكة'],
            )
            ->create();

        foreach ($branches as $branch) {
            // كل فرع له 3-5 مناطق
            $regions = Region::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create(['branch_id' => $branch->id]);

            foreach ($regions as $region) {
                // كل منطقة لها 4-8 مساجد
                $mosques = Mosque::factory()
                    ->count(fake()->numberBetween(4, 8))
                    ->create(['region_id' => $region->id]);

                foreach ($mosques as $mosque) {
                    // كل مسجد له 2-5 مراكز
                    // Center::factory()
                    //     ->count(fake()->numberBetween(2, 5))
                    //     ->create(['mosque_id' => $mosque->id]);
                }
            }
        }
    }
}