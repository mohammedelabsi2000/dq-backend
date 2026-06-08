<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelTrackCourse\LevelTrackSubjectRequest;
use App\Http\Resources\LevelTrackSubjectResource;
use App\Models\LevelTrack;
use App\Models\LevelTrackSubject;

class LevelTrackSubjectController extends Controller
{
    // ========================
    // GET /level-tracks/{levelTrack}/subjects
    // ========================
    public function index(LevelTrack $levelTrack)
    {
        $subjects = $levelTrack->levelTrackSubjects()
            ->with('subject')
            ->orderBy('order')
            ->get();

        return $this->success(
            LevelTrackSubjectResource::collection($subjects),
            'بيانات المساقات'
        );
    }

    // ========================
    // POST /level-track-subjects
    // إضافة مساق واحد
    // ========================
    public function store(LevelTrackSubjectRequest $request)
    {
        $levelTrackSubject = LevelTrackSubject::create($request->validated());

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject->load('subject', 'levelTrack')),
            'تم إضافة المساق بنجاح',
            201
        );
    }

    // ========================
    // GET /level-track-subjects/{levelTrackSubject}
    // ========================
    public function show(LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->load('subject', 'levelTrack.level', 'levelTrack.track');

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject),
            'بيانات المساق'
        );
    }

    // ========================
    // PATCH /level-track-subjects/{levelTrackSubject}
    // تعديل سجل واحد
    // ========================
    public function update(LevelTrackSubjectRequest $request, LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->update($request->validated());

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject->load('subject', 'levelTrack')),
            'تم تعديل المساق بنجاح'
        );
    }

    // ========================
    // DELETE /level-track-subjects/{levelTrackSubject}
    // ========================
    public function destroy(LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->delete();

        return $this->success(
            null,
            'تم حذف المساق من المسار بنجاح'
        );
    }
}
