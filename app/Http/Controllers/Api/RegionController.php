<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Region::query();

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->boolean('with_branch')) {
            $query->with('branch');
        }

        $perPage = $request->integer('per_page', 15);

        $regions = $query->latest()->paginate($perPage);

        return response()->json($regions, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRegionRequest $request)
    {
        $region = Region::create($request->validated());

        return response()->json([
            'message' => 'Region created successfully',
            'data' => $region
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(Request $request, Region $region)
    {
        if ($request->boolean('with_branch')) {
            $region->load('branch');
        }

        return response()->json($region, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRegionRequest $request, Region $region)
    {
        $region->update($request->validated());

        return response()->json([
            'message' => 'Region updated successfully',
            'data' => $region->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Region $region)
    {
        $region->delete();

        return response()->json([
            'message' => 'Region deleted successfully'
        ], 200);
    }
}
