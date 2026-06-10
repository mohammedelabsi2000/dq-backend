<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelTrackCourse\LevelTrackSubjectRequest;
use App\Http\Resources\LevelTrackSubjectResource;
use App\Models\LevelTrack;
use App\Models\LevelTrackSubject;
use Illuminate\Http\Request;

class LevelTrackSubjectController extends Controller
{
    /**
     * Display a listing of the level track subjects.
     *
     * GET /level-tracks/{levelTrack}/subjects
     *
     * @param Request $request
     * @param LevelTrack $levelTrack
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, LevelTrack $levelTrack)
    {
        // $this->authorize('viewAny', LevelTrackSubject::class);

        $query = $levelTrack->levelTrackSubjects();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $query->with('subject');

        $subjects = $query->get();

        return $this->successWithPagination(
            LevelTrackSubjectResource::collection($subjects),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * POST /level-track-subjects
     *
     * @param LevelTrackSubjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LevelTrackSubjectRequest $request)
    {
        $levelTrackSubject = LevelTrackSubject::create($request->validated());

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject->load('subject', 'levelTrack')),
            'تم إضافة المساق بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * GET /level-track-subjects/{levelTrackSubject}
     *
     * @param Request $request
     * @param LevelTrackSubject $levelTrackSubject
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, LevelTrackSubject $levelTrackSubject)
    {
        // $this->authorize('view', $levelTrackSubject);

        $levelTrackSubject->load('subject', 'levelTrack.level', 'levelTrack.track');

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject),
            'بيانات المساق'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * PATCH /level-track-subjects/{levelTrackSubject}
     *
     * @param LevelTrackSubjectRequest $request
     * @param LevelTrackSubject $levelTrackSubject
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(LevelTrackSubjectRequest $request, LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->update($request->validated());

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject->load('subject', 'levelTrack')),
            'تم تعديل المساق بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * DELETE /level-track-subjects/{levelTrackSubject}
     *
     * @param LevelTrackSubject $levelTrackSubject
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(LevelTrackSubject $levelTrackSubject)
    {
        // $this->authorize('delete', $levelTrackSubject);

        $levelTrackSubject->delete();

        return $this->success(
            null,
            'تم حذف المساق من المسار بنجاح'
        );
    }
}
