<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\PlanTrack;
use App\Models\PlanTrackCourse;
use App\Models\Track;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $query = Plan::query();
        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',

        ]);
        $query = $q['query'];
        $total = $q['count'];

        $plans = $query
        ->with(['PlanTracks.courses.track'])
        ->get();

         return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => PlanResource::collection($plans)
        ], 'success', 200);

        // $plans = Plan::latest()->get();
        // return view('plans.index', compact('plans'));
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer'
        ]);

        Plan::create($request->all());

        return redirect()->route('plans.index')
            ->with('success', 'تم إنشاء الخطة بنجاح');
    }

    public function show(Plan $plan)
    {
        return view('plans.show', compact('plan'));
    }
    // صفحة اختيار المسارات والدورات
    public function setup(Plan $plan)
    {
        $tracks = Track::with('courses')->get();
        $planTracks = $plan->planTracks()->with('courses')->get();

        return view('plans.setup', compact('plan', 'tracks', 'planTracks'));
    }

    // حفظ الاختيارات
    public function saveSetup(Request $request, Plan $plan)
    {
        $request->validate([
            'tracks' => 'required|array'
        ]);

        // مسح الإعدادات القديمة
        $plan->planTracks()->delete();

        foreach ($request->tracks as $trackId => $trackData) {
            $planTrack = PlanTrack::create([
                'plan_id' => $plan->id,
                'track_id' => $trackId,
                'is_required' => isset($trackData['is_required']),
                'weight' => $trackData['weight'] ?? 1
            ]);

            if (isset($trackData['courses'])) {
                foreach ($trackData['courses'] as $courseId => $courseData) {
                    PlanTrackCourse::create([
                        'plan_track_id' => $planTrack->id,
                        'course_id' => $courseId,
                        'is_required' => isset($courseData['is_required']),
                        'order' => $courseData['order'] ?? 1
                    ]);
                }
            }
        }

        return redirect()->route('plans.show', $plan->id)
            ->with('success', 'تم تركيب الخطة بنجاح');
    }

    // عرض قائمة الخطط المركبة
    public function showSetupIndex()
    {
        $plans = Plan::with('planTracks.courses')->get();
        return view('plans.setup_index', compact('plans'));
    }

    // عرض خطة مركبة واحدة
    public function showSetup(Plan $plan)
    {
        $plan->load('planTracks.courses.track');
        return view('plans.setup_show', compact('plan'));
    }

    // حذف خطة مركبة بالكامل
    public function deleteSetup(Plan $plan)
    {
        $plan->planTracks()->delete();
        return redirect()->route('plans.setup.show.index')
            ->with('success', 'تم حذف الخطة المركبة بنجاح');
    }

    // تعديل الخطة المركبة (يروح لصفحة setup)
    public function editSetup(Plan $plan)
    {
        return redirect()->route('plans.setup', $plan->id);
    }

    public function edit(Plan $plan)
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required',
            // 'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
        ]);

        $plan->update($request->all());

        return redirect()->route('plans.index')
            ->with('success', 'تم التعديل بنجاح');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return back()->with('success', 'تم الحذف');
    }
}
