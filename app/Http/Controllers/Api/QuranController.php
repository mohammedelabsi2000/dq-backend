<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quran\Juz;
use App\Models\Quran\Surah;

class QuranController extends Controller
{
    public function juz()
    {
        $query = Juz::query();
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'limit' => '*',
            'searchColumns' => ['id', 'name'],
            'orderColumn' => 'id',
        ]);

        $juz = $query->with(['start_surah', 'end_surah'])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
            'data' => $juz,
        ], 'success', 200);
    }

    public function surahs()
    {
        $query = Surah::query();
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'limit' => '*',
            'searchColumns' => ['id', 'name_ar'],
            'orderColumn' => 'id',
        ]);

        $surahs = $query->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
            'data' => $surahs,
        ], 'success', 200);
    }
}
