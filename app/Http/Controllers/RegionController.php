<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with('branch')->latest()->get();
        $branches = Branch::all(); // لاختيار الفرع عند الإضافة
        return view('regions.index', compact('regions', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.*' => 'required|string|max:255',
            'branch_id.*' => 'required|exists:branches,id',
            'notes.*' => 'nullable|string',
        ]);

        $regions = [];

        foreach ($request->name as $i => $name) {
            $regions[] = Region::create([
                'name' => $name,
                'branch_id' => $request->branch_id[$i],
                'notes' => $request->notes[$i] ?? ''
            ]);
        }

        return response()->json([
            'success' => true,
            'regions' => view('regions.index_table', ['regions' => $regions])->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string'
        ]);

        $region = Region::findOrFail($id);
        $region->update([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
            'notes' => $request->notes ?? ''
        ]);

        return response()->json([
            'success' => true,
            'region' => view('regions.single_row', ['region' => $region])->render()
        ]);
    }

    public function destroy($id)
    {
        Region::findOrFail($id)->delete();
        return response()->json(['success' => true, 'id' => $id]);
    }

    public function multiDelete(Request $request)
    {
        Region::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'ids' => $request->ids]);
    }

    public function search(Request $request)
    {
        $q = $request->q;
        $branch_id = $request->branch_id;

        $regions = Region::with('branch')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->when($branch_id, function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->latest()
            ->get();

        return view('regions.index_table', compact('regions'))->render();
    }
}
