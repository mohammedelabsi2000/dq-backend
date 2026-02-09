<?php
// app/Http/Controllers/Api/PlanLevelController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanLevel\StorePlanLevelRequest;
use App\Http\Requests\PlanLevel\UpdatePlanLevelRequest;
use App\Models\PlanLevel;
use Illuminate\Http\Request;

class PlanLevelController extends Controller
{
    // قائمة المستويات (مع خيارات soft delete)
    public function index(Request $request)
    {
        $query = PlanLevel::with(['plan', 'timeUnit', 'maxTimeUnit', 'minTimeUnit']);

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        return $query->orderBy('level_order')->paginate(10);
    }

    // إنشاء مستوى جديد
    public function store(StorePlanLevelRequest $request)
    {
        $level = PlanLevel::create($request->validated());
        return response()->json($level, 201);
    }

    // عرض مستوى معين
    public function show(PlanLevel $plan_level)
    {
        return $plan_level->load(['plan', 'timeUnit', 'maxTimeUnit', 'minTimeUnit']);
    }

    // تحديث مستوى
    public function update(UpdatePlanLevelRequest $request, PlanLevel $plan_level)
    {
        $plan_level->update($request->validated());
        return response()->json($plan_level);
    }

    // حذف soft delete
    public function destroy(PlanLevel $plan_level)
    {
        $plan_level->delete();
        return response()->json(['message' => 'Plan level deleted successfully']);
    }

    
    public function trashed()
    {
        return PlanLevel::onlyTrashed()->paginate(10);
    }

    // استرجاع مستوى محذوف
    public function restore($id)
    {
        $level = PlanLevel::withTrashed()->findOrFail($id);
        $level->restore();
        return response()->json(['message' => 'Plan level restored successfully']);
    }

    // حذف نهائي
    public function forceDelete($id)
    {
        $level = PlanLevel::withTrashed()->findOrFail($id);
        $level->forceDelete();
        return response()->json(['message' => 'Plan level permanently deleted']);
    }
}