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
     *
     */
    public function index()
    {
        /* return response()->json(
            ConstantType::get()
        ); */


        $constantTypes = ConstantType::with('constants')->get();

        return $this->apiResponse(
            ConstantTypeResource::collection($constantTypes),
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
