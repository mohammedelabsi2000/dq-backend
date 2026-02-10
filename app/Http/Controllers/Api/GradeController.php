<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(
            Grade::latest()->paginate(15),
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGradeRequest $request)
    {
        $grade = Grade::create($request->validated());

        return response()->json([
            'message' => 'Grade created successfully',
            'data' => $grade
        ], 201);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        return response()->json($grade, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $grade->update($request->validated());

        return response()->json([
            'message' => 'Grade updated successfully',
            'data' => $grade
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json([
            'message' => 'Grade deleted successfully'
        ], 200);
    }
}
