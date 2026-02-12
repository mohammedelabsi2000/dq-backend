<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * عرض كل الفروع
     */
    public function index()
    {
        $branches = Branch::latest()->paginate(15);
        return view('branches.index', compact('branches'));
    }

    /**
     * صفحة الاضافة
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * تخزين فرع جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'max_replacement_limit' => 'integer|min:0',
            'min_replacement_limit' => 'integer|min:0',
        ]);

        Branch::create([
            'name' => $request->name,
            'notes' => $request->notes,
            'max_replacement_limit' => $request->max_replacement_limit,
            'min_replacement_limit' => $request->min_replacement_limit,
        ]);

        return redirect()->route('branches.index')->with('success', 'تم اضافة الفرع بنجاح');
    }

    /**
     * عرض فرع واحد
     */
    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    /**
     * صفحة التعديل
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * تحديث الفرع
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'max_replacement_limit' => 'required|integer|min:0',
            'min_replacement_limit' => 'required|integer|min:0',
        ]);

        $branch->update([
            'name' => $request->name,
            'notes' => $request->notes,
            'max_replacement_limit' => $request->max_replacement_limit,
            'min_replacement_limit' => $request->min_replacement_limit,
        ]);

        return redirect()->route('branches.index')->with('success', 'تم تحديث الفرع');
    }

    /**
     * حذف الفرع
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'تم حذف الفرع');
    }
}
