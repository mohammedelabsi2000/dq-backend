<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\PlanTrack;
use App\Models\PlanTrackCourse;
use App\Models\Track;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    /**
     * Display a listing of plans
     */
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
            ->with(['planTracks.courses.track'])
            ->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => PlanResource::collection($plans)
        ], 'success', 200);
    }


    /**
     * Store plan
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            // 'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $plan = Plan::create($data);

        return $this->success(
            new PlanResource($plan),
            'تم إنشاء الخطة بنجاح',
            201
        );
    }


    /**
     * Show single plan
     */
    public function show(Plan $plan)
    {
        $plan->load(['planTracks.courses.track']);

        return $this->apiResponse(
            new PlanResource($plan),
            'success',
            200
        );
    }


    /**
     * Update plan
     */
    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string',
            // 'weight' => 'required|integer',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $plan->update($data);

        return $this->success(
            new PlanResource($plan->fresh()),
            'تم تحديث الخطة بنجاح'
        );
    }


    /**
     * Delete plan
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return $this->success(
            null,
            'تم حذف الخطة بنجاح'
        );
    }


    /*
    ======================================
    Plan Setup
    ======================================
    */

    /**
     * Get setup data
     */
    public function setup(Plan $plan)
    {
        $tracks = Track::with('courses')->get();

        $plan->load('planTracks.courses.track');

        return $this->apiResponse([
            'plan' => new PlanResource($plan),
            'tracks' => $tracks,
            'plan_tracks' => $plan->planTracks
        ], 'success');
    }


    /**
     * Save plan setup
     */
    public function saveSetup(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'tracks' => 'required|array'
        ]);

        // حذف القديم
        $plan->planTracks()->delete();

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

        $plan->load('planTracks.courses.track');

        return $this->success(
            new PlanResource($plan),
            'تم حفظ إعداد الخطة بنجاح'
        );
    }


    /**
     * Show all setups
     */
    public function setupIndex()
    {
        $plans = Plan::with('planTracks.courses.track')->get();

        return $this->apiResponse(
            PlanResource::collection($plans),
            'success'
        );
    }


    /**
     * Show one setup
     */
    public function showSetup(Plan $plan)
    {
        $plan->load('planTracks.courses.track');

        return $this->apiResponse(
            new PlanResource($plan),
            'success'
        );
    }


    /**
     * Delete setup
     */
    public function deleteSetup(Plan $plan)
    {
        $plan->planTracks()->delete();

        return $this->success(
            null,
            'تم حذف إعداد الخطة بنجاح'
        );
    }
}
