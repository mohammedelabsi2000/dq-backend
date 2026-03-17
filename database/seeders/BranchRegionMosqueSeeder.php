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
        // Creating 5 branches with specific names
        $branches = Branch::factory(5)
            ->sequence(
                ['name' => 'شمال غزة'],
                ['name' => 'شرق غزة'],
                ['name' => 'غرب غزة'],
                ['name' => 'جنوب غزة'],
                ['name' => 'الوسطى'],
                ['name' => 'خانيونس'],
                ['name' => 'رفح'],
            )
            ->create();

        foreach ($branches as $branch) {
            // Every branch has 3-5 regions
            $regions = Region::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create(['branch_id' => $branch->id]);

            foreach ($regions as $region) {
                // Every region has 4-8 mosques
                $mosques = Mosque::factory()
                    ->count(fake()->numberBetween(4, 8))
                    ->create(['region_id' => $region->id]);

                foreach ($mosques as $mosque) {
                    // Every mosque has 1-3 centers
                    $centers = Center::factory()
                        ->count(fake()->numberBetween(1, 3))
                        ->create([
                            'region_id' => $region->id,
                            'mosque_id' => $mosque->id,
                        ]);

                        // Every center has 1-3 halaqas
                        
                }
            }
        }
    }
}