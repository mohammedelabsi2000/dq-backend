<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Constant;

class ConstantController extends Controller
{

    // GET /api/v1/constants
    public function index()
    {
        return response()->json(
            Constant::with('parent')->get()
        );
    }

    // GET /api/v1/constants/{id}
    public function show($id)
    {
        $constant = Constant::with('parent')->findOrFail($id);

        return response()->json($constant);
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
