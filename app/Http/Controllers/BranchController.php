<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
{
    $branches = Branch::latest()->paginate(15);

    dd($branches);
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
}
