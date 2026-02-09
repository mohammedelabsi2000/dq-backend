<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMosqueRequest;
use App\Http\Requests\UpdateMosqueRequest;
use App\Models\Mosque;
use Illuminate\Http\Request;

class MosqueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Mosque::query();

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->boolean('with_region')) {
            $query->with('region');
        }

        $perPage = $request->integer('per_page', 15);

        return response()->json($query->latest()->paginate($perPage), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreMosqueRequest $request)
    {
        $mosque = Mosque::create($request->validated());

        return response()->json([
            'message' => 'Mosque created successfully',
            'data' => $mosque
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(Request $request, Mosque $mosque)
    {
        if ($request->boolean('with_region')) {
            $mosque->load('region');
        }

        return response()->json($mosque, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateMosqueRequest $request, Mosque $mosque)
    {
        $mosque->update($request->validated());

        return response()->json([
            'message' => 'Mosque updated successfully',
            'data' => $mosque->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Mosque $mosque)
    {
        $mosque->delete();

        return response()->json([
            'message' => 'Mosque deleted successfully'
        ], 200);
    }
}
