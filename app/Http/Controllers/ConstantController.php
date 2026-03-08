<?php

namespace App\Http\Controllers;

use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Http\Request;

class ConstantController extends Controller
{
    /**
     * Display a listing of constants
     */
    public function index()
    {
        $constants = Constant::with('type', 'parent')->latest()->paginate(15);
        return view('constants.index', compact('constants'));
    }

    /**
     * Show form to create a constant
     */
    public function create()
    {
        $types = ConstantType::all();
        $parents = Constant::all();
        return view('constants.create', compact('types', 'parents'));
    }

    /**
     * Store a new constant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'constant_type_id' => 'required|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        Constant::create([
            'name' => $request->name,
            'constant_type_id' => $request->constant_type_id,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'notes' => $request->notes,
        ]);

        return redirect()->route('constants.index')->with('success', 'تم إنشاء الثابت بنجاح');
    }

    /**
     * Show constant details
     */
    public function show(Constant $constant)
    {
        $constant->load('constantType', 'parent');
        return view('constants.show', compact('constant'));
    }

    /**
     * Show form to edit constant
     */
    public function edit(Constant $constant)
    {
        $types = ConstantType::all();
        $parents = Constant::where('id', '!=', $constant->id)->get();
        return view('constants.edit', compact('constant', 'types', 'parents'));
    }

    /**
     * Update constant
     */
    public function update(Request $request, Constant $constant)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'constant_type_id' => 'required|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $constant->update([
            'name' => $request->name,
            'constant_type_id' => $request->constant_type_id,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'notes' => $request->notes,
        ]);

        return redirect()->route('constants.index')->with('success', 'تم تحديث الثابت بنجاح');
    }

    /**
     * Delete constant
     */
    public function destroy(Constant $constant)
    {
        $constant->delete();
        return redirect()->route('constants.index')->with('success', 'تم حذف الثابت بنجاح');
    }
}
