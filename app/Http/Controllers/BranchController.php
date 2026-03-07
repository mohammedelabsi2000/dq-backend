<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $branches = Branch::latest()->paginate(15);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(StoreBranchRequest $request)
    {
        Branch::create($request->validated());

        return redirect()->route('branches.index')
            ->with('success', 'تم إنشاء الفرع بنجاح');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')
            ->with('success', 'تم تعديل الفرع بنجاح');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'تم حذف الفرع بنجاح');
    }

    // 🔥 حذف متعدد (AJAX)
    public function multiDelete(Request $request)
    {
        Branch::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }

    // 🔥 بحث (AJAX)
    public function search(Request $request)
    {
        $branches = Branch::where('name', 'like', "%{$request->q}%")
            ->latest()
            ->get();

        return view('branches.index_table', compact('branches'))->render();
    }
}
