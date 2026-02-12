<?php

namespace App\Http\Controllers;

use App\Models\ConstantType;
use Illuminate\Http\Request;

class ConstantTypeController extends Controller
{
    /**
     * Display a listing of constant types
     */
    public function index()
    {
        $types = ConstantType::latest()->paginate(15);
        return view('constant_types.index', compact('types'));
    }

    /**
     * Show form to create a new constant type
     */
    public function create()
    {
        return view('constant_types.create');
    }

    /**
     * Store a newly created constant type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:constant_types,name',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        ConstantType::create($request->all());

        return redirect()->route('constant-types.index')
            ->with('success', 'تم إنشاء نوع الثابت بنجاح');
    }

    /**
     * Show details of a constant type
     */
    public function show(ConstantType $constantType)
    {
        return view('constant_types.show', compact('constantType'));
    }

    /**
     * Show form to edit a constant type
     */
    public function edit(ConstantType $constantType)
    {
        return view('constant_types.edit', compact('constantType'));
    }

    /**
     * Update a constant type
     */
    public function update(Request $request, ConstantType $constantType)
    {
        $request->validate([
            'name' => 'required|string|unique:constant_types,name,' . $constantType->id,
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $constantType->update($request->all());

        return redirect()->route('constant-types.index')
            ->with('success', 'تم تحديث نوع الثابت بنجاح');
    }

    /**
     * Delete a constant type
     */
    public function destroy(ConstantType $constantType)
    {
        if ($constantType->constants()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف هذا النوع لوجود ثوابت مرتبطة به');
        }

        $constantType->delete();

        return redirect()->route('constant-types.index')
            ->with('success', 'تم حذف نوع الثابت بنجاح');
    }
}
