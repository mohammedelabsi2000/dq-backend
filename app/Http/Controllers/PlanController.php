<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Constant;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of plans
     */
    public function index()
    {
        $plans = Plan::with(['type', 'targetGroup'])->latest()->paginate(15);
        return view('plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        $types = Constant::all(); // نوع الخطة
        $targetGroups = Constant::all(); // الفئة المستهدفة
        return view('plans.create', compact('types', 'targetGroups'));
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:constants,id',
            'target_group_id' => 'required|exists:constants,id',
            'description' => 'nullable|string',
            'level_numbers' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        Plan::create($request->all());

        return redirect()->route('plans.index')->with('success', 'تم إنشاء الخطة بنجاح');
    }

    /**
     * Display the specified plan
     */
    public function show(Plan $plan)
    {
        $plan->load(['type', 'targetGroup']);
        return view('plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified plan
     */
    public function edit(Plan $plan)
    {
        $types = Constant::all();
        $targetGroups = Constant::all();
        return view('plans.edit', compact('plan', 'types', 'targetGroups'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:constants,id',
            'target_group_id' => 'required|exists:constants,id',
            'description' => 'nullable|string',
            'level_numbers' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $plan->update($request->all());

        return redirect()->route('plans.index')->with('success', 'تم تحديث الخطة بنجاح');
    }

    /**
     * Remove the specified plan
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('plans.index')->with('success', 'تم حذف الخطة بنجاح');
    }
}
