<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstantTypeResource;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\ConstantType;


class ConstantTypeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $constantTypes = ConstantType::with('constants')->get();

        return $this->apiResponse([
            'data' => ConstantTypeResource::collection($constantTypes),
        ], 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ConstantType $constantType)
    {
        $constantType = $constantType->load([
            'constants'
        ]);
        return $this->apiResponse([
            'data' => ConstantTypeResource::collection($constantType),
        ], 'success', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        //
    }
}
