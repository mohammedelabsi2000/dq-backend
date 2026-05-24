<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plan\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('levels')->latest()->paginate(15);

        return response()->json(PlanResource::collection($plans)->response()->getData(true));
    }

    public function store(PlanRequest $request): JsonResponse
    {
        $plan = Plan::create($request->validated());

        return response()->json(new PlanResource($plan), 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackCourses.course');

        return response()->json(new PlanResource($plan));
    }

    public function update(PlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json(new PlanResource($plan));
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(['message' => 'تم حذف الخطة بنجاح']);
    }

    public function toggleActive(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'message'   => $plan->is_active ? 'تم تفعيل الخطة' : 'تم تعطيل الخطة',
            'data'      => new PlanResource($plan),
        ]);
    }
}
