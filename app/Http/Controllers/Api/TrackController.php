<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Track\TrackRequest;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Track::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $tracks = $query->withCount('subjects')->get();

        return $this->successWithPagination(
            TrackResource::collection($tracks),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    public function store(TrackRequest $request): JsonResponse
    {
        $data = $request->validated();
        $track = Track::create($data);

        return $this->success(new TrackResource($track), 'تم إنشاء المسار بنجاح', 201);
    }

    public function show(Track $track): JsonResponse
    {
        $track->load('subjects');

        return $this->success(new TrackResource($track), 'تم جلب المسار بنجاح');
    }

    public function update(TrackRequest $request, Track $track): JsonResponse
    {
        $data = $request->validated();
        $track->update($data);

        return $this->success(new TrackResource($track), 'تم تحديث بيانات المسار بنجاح');
    }

    public function destroy(Track $track): JsonResponse
    {
        $track->delete();
        return $this->success(null, 'تم حذف المسار بنجاح');
    }
}
