<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('levels')->get();
        return $this->successWithPagination(PlanResource::collection($plans));
    }

    public function store(PlanRequest $request): JsonResponse
    {
        $plan = Plan::create($request->validated());

        return $this->success(new PlanResource($plan), 'تم إنشاء الخطة بنجاح', 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        return $this->success(new PlanResource($plan));
    }

    public function update(PlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());
        return $this->success(new PlanResource($plan), 'تم تحديث الخطة بنجاح');
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return $this->success(null, 'تم حذف الخطة بنجاح');
    }

    public function toggleActive(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return $this->success(new PlanResource($plan), $plan->is_active ? 'تم تفعيل الخطة' : 'تم تعطيل الخطة');
    }
}
