<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Track\TrackRequest;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class TrackController extends Controller
{
    public function index(): JsonResponse
    {
        $tracks = Track::withCount('subjects')->latest()->paginate(15);

        return $this->successWithPagination(TrackResource::collection($tracks), [
            'total' => $tracks->total(),
            'skip' => $tracks->perPage(),
            'limit' => $tracks->perPage(),
        ], 'تم جلب المسارات بنجاح');
    }

    public function store(TrackRequest $request): JsonResponse
    {
        $track = Track::create($request->validated());

        return $this->success(new TrackResource($track), 'تم إنشاء المسار بنجاح', 201);
    }

    public function show(Track $track): JsonResponse
    {
        $track->load('subjects');

        return $this->success(new TrackResource($track), 'تم جلب المسار بنجاح');
    }

    public function update(TrackRequest $request, Track $track): JsonResponse
    {
        $track->update($request->validated());

        return $this->success(new TrackResource($track), 'تم تحديث المسار بنجاح');
    }

    public function destroy(Track $track): JsonResponse
    {
        $track->delete();
        return $this->success(null, 'تم حذف المسار بنجاح');
    }
}
