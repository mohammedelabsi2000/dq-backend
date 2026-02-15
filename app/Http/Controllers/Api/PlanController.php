<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanTrack;
use App\Models\PlanTrackCourse;
use App\Models\Track;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::query();

        if ($request->boolean('with_setup')) {
            $query->with('planTracks.courses');
        }

        $perPage = $request->integer('per_page', 15);

return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer'
        ]);

        $plan = Plan::create($data);

        return response()->json([
            'message' => 'Plan created successfully',
            'data' => $plan
        ], 201);
    }

    public function show(Request $request, Plan $plan)
    {
        if ($request->boolean('with_setup')) {
            $plan->load('planTracks.courses.track');
        }

        return response()->json($plan, 200);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer'
        ]);

        $plan->update($data);

        return response()->json([
            'message' => 'Plan updated successfully',
            'data' => $plan->fresh()
        ], 200);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully'
        ], 200);
    }

    // جلب صفحة setup (المسارات + الدورات + إعدادات الخطة)
    public function setup(Plan $plan)
    {
        $tracks = Track::with('courses')->get();
        $planTracks = $plan->planTracks()->with('courses')->get();

        return response()->json([
            'plan' => $plan,
            'tracks' => $tracks,
            'plan_tracks' => $planTracks
        ], 200);
    }

    // حفظ تركيب الخطة
    public function saveSetup(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'tracks' => 'required|array'
        ]);

        $plan->planTracks()->delete();

        foreach ($data['tracks'] as $trackId => $trackData) {

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

        return response()->json([
            'message' => 'Plan setup saved successfully'
        ], 200);
    }

    // عرض كل الخطط المركبة
    public function setupIndex()
    {
        $plans = Plan::with('planTracks.courses')->get();

        return response()->json($plans, 200);
    }

    // عرض خطة مركبة واحدة
    public function showSetup(Plan $plan)
    {
        $plan->load('planTracks.courses.track');

        return response()->json($plan, 200);
    }

    // حذف التركيب
    public function deleteSetup(Plan $plan)
    {
        $plan->planTracks()->delete();

        return response()->json([
            'message' => 'Plan setup deleted successfully'
        ], 200);
    }
}
