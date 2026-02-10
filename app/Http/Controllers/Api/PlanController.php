<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return Plan::with(['type', 'targetGroup'])->paginate(10);
    }

    public function store(StorePlanRequest $request)
    {
        $plan = Plan::create($request->validated());

        return response()->json($plan, 201);
    }

    public function show(Plan $plan)
    {
        return $plan->load(['type', 'targetGroup']);
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        return response()->json($plan);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json(['message' => 'Plan deleted successfully']);
    }
    public function trashed()
    {
        return Plan::onlyTrashed()->paginate(10);
    }
    public function restore($id)
    {
        $plan = Plan::onlyTrashed()->findOrFail($id);
        $plan->restore();

        return response()->json([
            'message' => 'Plan restored successfully'
        ]);
    }
    public function forceDelete($id)
    {
        $plan = Plan::onlyTrashed()->findOrFail($id);
        $plan->forceDelete();

        return response()->json([
            'message' => 'Plan permanently deleted'
        ]);
    }
}