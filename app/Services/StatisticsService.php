<?php

namespace App\Services;

use App\Models\{
    // User,
    Student,
    Halaqa,
    Branch,
    Region,
    Mosque,
    Center
};
use Illuminate\Support\Facades\Cache;

class StatisticsService
{

    /**
     * Get various statistics about the application.
     * This method retrieves counts of models, and caches the results for 5 minutes to improve performance.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return Cache::remember('statistics', 5, function () {
            return [
                // 'users_count' => User::onlyTeachers()->count(),
                'students_count' => Student::count(),
                'halaqat_count' => Halaqa::count(),
                'branches_count' => Branch::count(),
                'regions_count' => Region::count(),
                'mosques_count' => Mosque::count(),
                'centers_count' => Center::count(),
            ];
        });
    }
}
