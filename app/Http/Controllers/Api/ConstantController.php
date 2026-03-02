<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstantResource;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConstantController extends Controller
{
    use ApiResponser;

    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Constant::query();
        // لإرجاع قائمة بالثوابت من نوع مخصص
        if ($request->filled('with_type_name')) {
            try {
                $constType = ConstantType::where(
                    'name',
                    'like',
                    request()->get('with_type_name')
                )->firstOrFail();
            } catch (ModelNotFoundException $th) {
                return $this->error('نوع الثوابت هذا غير موجود في النظام');
            }
            $query->where('constant_type_id', '=', intval($constType['id']));
            $constants = $query->get(['id', 'name']);
            return $this->apiResponse([
                'data' => $constants,
            ], 'success', 200);
        }
        
        // لإرجاع جميع ثوابت النظام
        $constants = $query->with('parent')->get();

        return $this->apiResponse([
            'data' => ConstantResource::collection($constants),
        ], 'success', 200);
    }

    // GET /api/v1/constants/{id}
    public function show(Constant $constant)
    {
        $constant = $constant->load([
            'parent'
        ]);

        return $this->apiResponse([
            'data' => new ConstantResource($constant),
        ], 'success', 200);
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
