<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstantResource;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\Constant;

class ConstantController extends Controller
{
    use ApiResponser;

    // GET /api/v1/constants
    public function index()
    {
        $constants = Constant::with('parent')->get();

        return $this->apiResponse(
            ConstantResource::collection($constants),
            'success',
            200
        );
    }

    // GET /api/v1/constants/{id}
    public function show(Constant $constant)
    {
        $constant = $constant->load([
            'parent'
        ]);

        return $this->apiResponse(
            new ConstantResource($constant),
            'success',
            200
        );
    }

    // POST /api/v1/constants
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'constant_type_id' => 'required|max:100|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $constant = Constant::create($data);

        return response()->json($constant, 201);
    }

    // PUT / PATCH /api/v1/constants/{id}
    public function update(Request $request, $id)
    {
        $constant = Constant::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'constant_type_id' => 'sometimes|required|max:100|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $constant->update($data);

        return response()->json($constant);
    }

    // DELETE /api/v1/constants/{id}
    public function destroy($id)
    {
        $constant = Constant::findOrFail($id);
        $constant->delete();

        return response()->json([
            'message' => 'Constant deleted successfully'
        ]);
    }
}
