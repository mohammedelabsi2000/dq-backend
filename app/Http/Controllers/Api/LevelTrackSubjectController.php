<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelTrackSubjectRequest;
use App\Http\Resources\LevelTrackSubjectResource;
use App\Models\LevelTrack;
use App\Models\LevelTrackSubject;
use Illuminate\Http\JsonResponse;

class LevelTrackSubjectController extends Controller
{
    public function index(LevelTrack $levelTrack): JsonResponse
    {
        $subjects = $levelTrack->levelTrackSubjects()->with('subject')->get();

        return response()->json(LevelTrackSubjectResource::collection($subjects));
    }

    public function store(LevelTrackSubjectRequest $request): JsonResponse
    {
        $levelTrackSubject = LevelTrackSubject::create($request->validated());

        return response()->json(
            new LevelTrackSubjectResource($levelTrackSubject->load('subject', 'levelTrack')),
            201
        );
    }

    public function show(LevelTrackSubject $levelTrackSubject): JsonResponse
    {
        $levelTrackSubject->load('subject', 'levelTrack.level', 'levelTrack.track');

        return response()->json(new LevelTrackSubjectResource($levelTrackSubject));
    }

    public function update(LevelTrackSubjectRequest $request, LevelTrackSubject $levelTrackSubject): JsonResponse
    {
        $levelTrackSubject->update($request->validated());

        return response()->json(new LevelTrackSubjectResource($levelTrackSubject));
    }

    public function destroy(LevelTrackSubject $levelTrackSubject): JsonResponse
    {
        $levelTrackSubject->delete();

        return response()->json(['message' => 'تم حذف المساق من المستوى بنجاح']);
    }
}
