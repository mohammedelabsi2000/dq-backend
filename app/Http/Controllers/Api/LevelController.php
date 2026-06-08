<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\StoreLevelRequest;
use App\Http\Requests\Level\UpdateLevelRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{
    /**
     * Display a listing of the levels.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Level::class);

        $query = Level::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'order',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب الخطة
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->integer('plan_id'));
        }

        $query->with('plan','levelTracks.track', 'levelTracks.levelTrackSubjects.subject');

        $levels = $query->with('levelTracks')->withCount('levelTracks')->get();

        return $this->successWithPagination(
            LevelResource::collection($levels),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLevelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLevelRequest $request)
    {
        $validated = $request->validated();
        $tracksData = $validated['tracks'] ?? null;

        unset($validated['tracks']);

        $level = Level::create($validated);

        // إضافة المسارات إذا وجدت
        if ($tracksData && is_array($tracksData)) {
            foreach ($tracksData as $trackData) {
                $level->levelTracks()->create([
                    'track_id' => $trackData['track_id'],
                    'weight' => $trackData['weight'],
                    'order' => $trackData['order'],
                ]);
            }
        }

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_plan')) {
            $level->load('plan');
        }

        if ($request->boolean('with_tracks')) {
            $level->load('levelTracks.track', 'levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new LevelResource($level->loadCount('levelTracks')),
            'تم إنشاء المستوى بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Level $level
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Level $level)
    {
        $this->authorize('view', $level);

        $level->load('plan');

        // تحميل شجرة المسارات حسب الطلب
        if ($request->boolean('with_tracks')) {
            $level->load('levelTracks.track', 'levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new LevelResource($level->loadCount('levelTracks')),
            'بيانات المستوى'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLevelRequest $request
     * @param Level $level
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateLevelRequest $request, Level $level)
    {
        $validated = $request->validated();
        $tracksData = $validated['tracks'] ?? null;

        unset($validated['tracks']);

        $level->update($validated);

        // تحديث المسارات إذا وجدت
        if ($tracksData !== null && is_array($tracksData)) {
            // حذف المسارات القديمة
            $level->levelTracks()->delete();

            // إضافة المسارات الجديدة
            foreach ($tracksData as $trackData) {
                $level->levelTracks()->create([
                    'track_id' => $trackData['track_id'],
                    'weight' => $trackData['weight'],
                    'order' => $trackData['order'],
                ]);
            }
        }

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_plan')) {
            $level->load('plan');
        }

        if ($request->boolean('with_tracks')) {
            $level->load('levelTracks.track', 'levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new LevelResource($level->loadCount('levelTracks')),
            'تم تحديث المستوى بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Level $level
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Level $level)
    {
        $this->authorize('delete', $level);

        // تحقق من وجود مسارات تابعة قبل الحذف
        if ($level->levelTracks()->exists()) {
            return $this->error(
                'لا يمكن حذف المستوى لأنه يحتوي على مسارات تابعة',
                400
            );
        }

        $level->delete();

        return $this->success(
            null,
            'تم حذف المستوى بنجاح'
        );
    }

    /**
     * Reorder a set of levels.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        $this->authorize('update', Level::class);

        $items = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:levels,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ])['items'];

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                Level::whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return $this->success(
            null,
            'تم تحديث الترتيب بنجاح'
        );
    }
}
