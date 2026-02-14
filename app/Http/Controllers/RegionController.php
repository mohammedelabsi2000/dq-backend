<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Branch;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Display a listing of regions.
     */
    public function index()
    {
        $regions = Region::with('branch')->latest()->paginate(15);
        return view('regions.index', compact('regions'));
    }

    /**
     * Show the form for creating a new region.
     */
    public function create()
    {
        $branches = Branch::all();
        return view('regions.create', compact('branches'));
    }

    /**
     * Store a newly created region in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        Region::create($request->all());

        return redirect()->route('regions.index')->with('success', 'تم إنشاء المنطقة بنجاح');
    }

    /**
     * Display the specified region.
     */
    public function show(Region $region)
    {
        $region->load('branch');
        return view('regions.show', compact('region'));
    }

    /**
     * Show the form for editing the specified region.
     */
    public function edit(Region $region)
    {
        $branches = Branch::all();
        return view('regions.edit', compact('region', 'branches'));
    }

    /**
     * Update the specified region in storage.
     */
    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        $region->update($request->all());

        return redirect()->route('regions.index')->with('success', 'تم تحديث المنطقة بنجاح');
    }

    /**
     * Remove the specified region from storage.
     */
    public function destroy(Region $region)
    {
        // منع الحذف إذا هناك مساجد مرتبطة
        if ($region->mosques()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المنطقة لوجود مساجد مرتبطة بها');
        }

        $region->delete();
        return redirect()->route('regions.index')->with('success', 'تم حذف المنطقة بنجاح');
    }
}
