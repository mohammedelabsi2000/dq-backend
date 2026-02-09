<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCenterRequest;
use App\Http\Requests\UpdateCenterRequest;
use App\Models\Center;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Center::query();

        if ($request->filled('mosque_id')) {
            $query->where('mosque_id', $request->integer('mosque_id'));
        }

        if ($request->boolean('with_mosque')) {
            $query->with('mosque');
        }

        return response()->json(
            $query->latest()->paginate(15),
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCenterRequest $request)
    {
        $center = Center::create($request->validated());

        return response()->json([
            'message' => 'Center created successfully',
            'data' => $center
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Center $center)
    {
        if ($request->boolean('with_mosque')) {
            $center->load('mosque');
        }

        return response()->json($center, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCenterRequest $request, Center $center)
    {
        $center->update($request->validated());

        return response()->json([
            'message' => 'Center updated successfully',
            'data' => $center->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Center $center)
    {
        $center->delete();

        return response()->json([
            'message' => 'Center deleted successfully'
        ], 200);
    }
}
