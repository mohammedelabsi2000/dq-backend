<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'ممتاز', 'DQ_range_from' => '90', 'DQ_range_to' => '100'],
            ['name' => 'جيد جداً', 'DQ_range_from' => '80', 'DQ_range_to' => '89.99'],
            ['name' => 'جيد', 'DQ_range_from' => '70', 'DQ_range_to' => '79.99'],
            ['name' => 'مقبول', 'DQ_range_from' => '60', 'DQ_range_to' => '69.99'],
            ['name' => 'ضعيف', 'DQ_range_from' => '50', 'DQ_range_to' => '59.99'],
            ['name' => 'راسب', 'DQ_range_from' => '0', 'DQ_range_to' => '49.99'],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}