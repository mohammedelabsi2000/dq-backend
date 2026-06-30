<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomJuzResource;
use App\Http\Requests\CustomJuz\StoreCustomJuzRequest;
use App\Http\Requests\CustomJuz\UpdateCustomJuzRequest;
use App\Models\Quran\CustomJuz;
use App\Models\Quran\Surah;

class CustomJuzController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CustomJuz::class);
        $query = CustomJuz::query();
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'sort_order',
        ]);

        $juz = $query->get();

        return $this->successWithPagination(
            CustomJuzResource::collection($juz),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    public function store(StoreCustomJuzRequest $request)
    {
        $juz = CustomJuz::create($request->validated());
        return $this->success($juz, 'تم إنشاء الجزء بنجاح');
    }

    public function show(CustomJuz $juz)
    {
        $this->authorize('view', $juz);
        return $this->success($juz);
    }

    public function update(UpdateCustomJuzRequest $request, CustomJuz $juz)
    {
        $juz->update($request->validated());
        return $this->success($juz, 'تم تحديث بيانات الجزء بنجاح');
    }

    public function destroy(CustomJuz $juz)
    {
        $this->authorize('delete', $juz);
        $juz = $juz->delete();

        return $this->success($juz, 'تم حذف الجزء بنجاح');
    }

    public function surahs(CustomJuz $juz)
    {
        $this->authorize('view', $juz);

        $juz->load(['start_surah', 'end_surah']);

        // Get all surahs in range
        $surahIds = range($juz->start_surah_id, $juz->end_surah_id);
        $surahs = Surah::whereIn('id', $surahIds)->get();

        $surahData = [];
        foreach ($surahs as $surah) {
            $surahData[] = [
                'id' => $surah->id,
                'name_ar' => $surah->name_ar,
                'name_en' => $surah->name_en,
                'verse_range' => [
                    'start_aya' => $surah->id == $juz->start_surah_id ? $juz->start_aya : 1,
                    'end_aya' => $surah->id == $juz->end_surah_id ? $juz->end_aya : null,
                ],
            ];
        }

        return $this->success([
            'custom_juz' => [
                'id' => $juz->id,
                'name' => $juz->name,
                'start_surah_id' => $juz->start_surah_id,
                'end_surah_id' => $juz->end_surah_id,
            ],
            'surahs' => $surahData,
        ], 'success', 200);
    }
}
