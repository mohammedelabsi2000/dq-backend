<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelTrackRequest;
use App\Http\Resources\LevelTrackResource;
use App\Models\Level;
use App\Models\LevelTrack;
use Illuminate\Http\JsonResponse;

class LevelTrackController extends Controller
{
    public function index(Level $level): JsonResponse
    {
        $levelTracks = $level->levelTracks()->with('track', 'levelTrackSubjects.subject')->get();

        return response()->json(LevelTrackResource::collection($levelTracks));
    }

    public function store(LevelTrackRequest $request): JsonResponse
    {
        $currentTotal = LevelTrack::where('level_id', $request->level_id)->sum('weight');

        if (($currentTotal + $request->weight) > 100) {
            return response()->json([
                'message' => 'مجموع الأوزان يتجاوز 100%، الوزن المتبقي: ' . (100 - $currentTotal) . '%',
            ], 422);
        }

        $levelTrack = LevelTrack::create($request->validated());

        return response()->json(new LevelTrackResource($levelTrack->load('track', 'level')), 201);
    }

    public function show(LevelTrack $levelTrack): JsonResponse
    {
        $levelTrack->load('level', 'track', 'levelTrackSubjects.subject');

        return response()->json(new LevelTrackResource($levelTrack));
    }

    public function update(LevelTrackRequest $request, LevelTrack $levelTrack): JsonResponse
    {
        $currentTotal = LevelTrack::where('level_id', $request->level_id)
            ->where('id', '!=', $levelTrack->id)
            ->sum('weight');

        if (($currentTotal + $request->weight) > 100) {
            return response()->json([
                'message' => 'مجموع الأوزان يتجاوز 100%، الوزن المتبقي: ' . (100 - $currentTotal) . '%',
            ], 422);
        }

        $levelTrack->update($request->validated());

        return response()->json(new LevelTrackResource($levelTrack));
    }

    public function destroy(LevelTrack $levelTrack): JsonResponse
    {
        $levelTrack->delete();

        return response()->json(['message' => 'تم حذف المسار من المستوى بنجاح']);
    }
}
