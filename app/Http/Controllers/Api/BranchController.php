<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        return response()->json(Branch::latest()->paginate(15), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(Branch $branch)
    {
        return response()->json($branch, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return response()->json([
            'message' => 'Branch updated successfully',
            'data' => $branch
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully'
        ], 200);
    }
}
