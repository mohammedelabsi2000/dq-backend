<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{

    public function index(Request $request)
    {
        $query = Track::query();
        $perPage = $request->integer('per_page', 15);

        $tracks = $query->latest()->paginate($perPage);

        return $this->apiResponse(
            TrackResource::collection($tracks),
            'تم جلب المسارات بنجاح',
            200
        );
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);

        $track = Track::create($request->all());

        return $this->apiResponse(
            new TrackResource($track),
            'تم إنشاء المسار',
            201
        );
    }

    public function show(Track $track)
    {
        return $this->apiResponse(
            new TrackResource($track),
            'تم جلب المسار',
            200
        );
    }

    public function update(Request $request, Track $track)
    {
        $request->validate(['name' => 'required']);

        $track->update($request->all());

        return $this->apiResponse(
            new TrackResource($track->fresh()),
            'تم التعديل',
            200
        );
    }


    /**
     * Remove the specified track.
     */
    public function destroy(Track $track)
    {
        $track->delete();

        return $this->apiResponse(
            null,
            'تم الحذف',
            200
        );
    }
}
