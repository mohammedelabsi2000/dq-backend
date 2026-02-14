<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MosqueController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $mosques = Mosque::latest()->get();
        return view('mosques.index', compact('branches', 'mosques'));
    }

    public function getRegions(Request $request)
    {
        $regions = Region::where('branch_id', $request->branch_id)->get();
        return response()->json($regions);
    }

    public function search(Request $request)
    {
        $mosques = Mosque::where('name', 'like', '%' . $request->q . '%')
            ->orWhereHas('region.branch', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            })->latest()->get();
        return view('mosques.partials.table', compact('mosques'))->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.*' => 'required|string|max:255',
            'region_id.*' => 'required|exists:regions,id'
        ]);

        foreach ($request->name as $i => $name) {
            Mosque::create([
                'name' => $name,
                'notes' => $request->notes[$i] ?? null,
                'region_id' => $request->region_id[$i]
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Mosque $mosque)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id'
        ]);
        $mosque->update($request->only('name', 'notes', 'region_id'));
        return response()->json(['success' => true]);
    }

    public function destroy(Mosque $mosque)
    {
        $mosque->delete();
        return response()->json(['success' => true]);
    }

    public function destroyMultiple(Request $request)
    {
        Mosque::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }
}
