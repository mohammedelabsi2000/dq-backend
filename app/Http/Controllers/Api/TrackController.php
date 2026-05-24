<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackRequest;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class TrackController extends Controller
{
    public function index(): JsonResponse
    {
        $tracks = Track::withCount('subjects')->latest()->paginate(15);

        return response()->json(TrackResource::collection($tracks)->response()->getData(true));
    }

    public function store(TrackRequest $request): JsonResponse
    {
        $track = Track::create($request->validated());

        return response()->json(new TrackResource($track), 201);
    }

    public function show(Track $track): JsonResponse
    {
        $track->load('subjects');

        return response()->json(new TrackResource($track));
    }

    public function update(TrackRequest $request, Track $track): JsonResponse
    {
        $track->update($request->validated());

        return response()->json(new TrackResource($track));
    }

    public function destroy(Track $track): JsonResponse
    {
        $track->delete();

        return response()->json(['message' => 'تم حذف المسار بنجاح']);
    }
}
