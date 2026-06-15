<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Display a listing of the plans.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Plan::class);

        $query = Plan::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب حالة التفعيل
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $query->with('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        $plans = $query->withCount('levels')->get();

        return $this->successWithPagination(
            PlanResource::collection($plans),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PlanRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PlanRequest $request)
    {
        $plan = Plan::create($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new PlanResource($plan->loadCount('levels')),
            'تم إنشاء الخطة بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    // public function show(Request $request, Plan $plan)
    // {
    //     // $this->authorize('view', $plan);

    //     $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');

    //     return $this->success(
    //         new PlanResource($plan->loadCount('levels')),
    //         'بيانات الخطة'
    //     );
    // }
    public function show(Request $request, Plan $plan)
    {
        // $this->authorize('view', $plan);

        // تحميل الخطة مع مستوياتها، ومسارات كل مستوى ومواده، مع أعداد العلاقات
        $plan->load([
            'levels' => function ($query) {
                $query->withCount('levelTracks')
                    ->with([
                        'levelTracks.track',
                        'levelTracks.levelTrackSubjects.subject',
                    ]);
            },
        ])->loadCount('levels');

        return $this->success(
            new PlanResource($plan),
            'بيانات الخطة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PlanRequest $request
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new PlanResource($plan->loadCount('levels')),
            'تم تحديث الخطة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Plan $plan)
    {
        // $this->authorize('delete', $plan);

        // تحقق من وجود مستويات تابعة قبل الحذف
        if ($plan->levels()->exists()) {
            return $this->error(
                'لا يمكن حذف الخطة لأنها تحتوي على مستويات تابعة',
                400
            );
        }

        $plan->delete();

        return $this->success(
            null,
            'تم حذف الخطة بنجاح'
        );
    }

    /**
     * Toggle the active state of the plan.
     *
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return $this->success(
            new PlanResource($plan),
            $plan->is_active ? 'تم تفعيل الخطة' : 'تم تعطيل الخطة'
        );
    }

    /**
     * Reorder a set of plans.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        // $this->authorize('update', Plan::class);

        $items = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:plans,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ])['items'];

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                Plan::whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return $this->success(
            null,
            'تم تحديث الترتيب بنجاح'
        );
    }
}
