<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Display a listing of the tracks.
     */
    public function index(Request $request)
    {
        $query = Track::query();

        $perPage = $request->integer('per_page', 15);

        return response()->json($query->latest()->paginate($perPage), 200);
    }

    /**
     * Store a newly created track.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $track = Track::create($request->all());

        return response()->json([
            'message' => 'تم إنشاء المسار',
            'data' => $track
        ], 201);
    }

    /**
     * Display the specified track.
     */
    public function show(Request $request, Track $track)
    {
        return response()->json($track, 200);
    }

    /**
     * Update the specified track.
     */
    public function update(Request $request, Track $track)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $track->update($request->all());

        return response()->json([
            'message' => 'تم التعديل',
            'data' => $track->fresh()
        ], 200);
    }

    /**
     * Remove the specified track.
     */
    public function destroy(Track $track)
    {
        $track->delete();

        return response()->json([
            'message' => 'تم الحذف'
        ], 200);
    }
}
