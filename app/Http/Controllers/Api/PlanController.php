<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Traits\ApiResponser;
use App\Models\Plan;
use App\Models\PlanTrack;
use App\Models\PlanTrackCourse;
use App\Models\Track;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $query = Plan::query();

        if ($request->boolean('with_setup')) {
            $query->with('planTracks.courses.track');
        }

        $plans = $query->latest()->get();

        return $this->apiResponse(
            PlanResource::collection($plans),
            'Plans retrieved successfully',
            200
        );
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

        return $this->apiResponse(
            new PlanResource($plan),
            'Plan created successfully',
            201
        );
    }

    public function show(Request $request, Plan $plan)
    {
        if ($request->boolean('with_setup')) {
            $plan->load('planTracks.courses.track');
        }

        return $this->apiResponse(
            new PlanResource($plan),
            'Plan retrieved successfully',
            200
        );
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

        return $this->apiResponse(
            new PlanResource($plan->fresh()),
            'Plan updated successfully',
            200
        );
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return $this->apiResponse(null, 'Plan deleted successfully', 200);
    }

    // ================================
    // Setup routes
    // ================================

    public function setup(Plan $plan)
    {
        $tracks = Track::with('courses')->get();
        $planTracks = $plan->planTracks()->with('courses.track')->get();

        return $this->apiResponse([
            'plan' => new PlanResource($plan->load('planTracks.courses.track')),
            'tracks' => $tracks,
            'plan_tracks' => $planTracks
        ], 'Plan setup retrieved successfully', 200);
    }

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

        foreach ($data['tracks'] as $trackId => $trackData) {
            $planTrack = PlanTrack::create([
                'plan_id' => $plan->id,
                'track_id' => $trackId,
                'is_required' => isset($trackData['is_required']),
                'weight' => $trackData['weight'] ?? 1
            ]);

            if (!empty($trackData['courses'])) {
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

    // تحميل العلاقات بعد الحفظ
    $plan->load('planTracks.courses.track');

    return response()->json([
        'message' => 'Plan setup saved successfully',
        'data' => $plan
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

        return $this->apiResponse(
            new PlanResource($plan),
            'Plan setup saved successfully',
            200
        );
    }

    public function deleteSetup(Plan $plan)
    {
        $plan->planTracks()->delete();

        return $this->apiResponse(null, 'Plan setup deleted successfully', 200);
    }
}
