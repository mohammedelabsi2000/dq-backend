<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackRequest;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class TrackController extends Controller
{
    public function index(): JsonResponse
    {
        $tracks = Track::withCount('subjects')->latest()->paginate(15);

        return $this->successWithPagination(TrackResource::collection($tracks)->response()->getData(true));
    }

    public function store(TrackRequest $request): JsonResponse
    {
        $track = Track::create($request->validated());

        return $this->success(new TrackResource($track), 201);
    }

    public function show(Track $track): JsonResponse
    {
        $track->load('subjects');

        return $this->success(new TrackResource($track));
    }

    public function update(TrackRequest $request, Track $track): JsonResponse
    {
        $track->update($request->validated());

        return $this->success(new TrackResource($track));
    }

    public function destroy(Track $track): JsonResponse
    {
        $track->delete();

        return $this->success(['message' => 'تم حذف المسار بنجاح']);
    }
}
