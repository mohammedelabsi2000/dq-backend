<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $branches = Branch::latest()->get();
        return view('branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.*' => 'required|string|max:255',
            'min_replacement_limit.*' => 'nullable|numeric',
            'max_replacement_limit.*' => 'nullable|numeric',
        ]);

        $branches = [];
        foreach ($request->name as $i => $name) {
            $branches[] = Branch::create([
                'name' => $name,
                'notes' => $request->notes[$i] ?? '',
                'min_replacement_limit' => (int) ($request->min_replacement_limit[$i] ?? 0),
                'max_replacement_limit' => (int) ($request->max_replacement_limit[$i] ?? 0),
            ]);
        }

        return response()->json([
            'success' => true,
            'branches' => view('branches.index_table', ['branches' => $branches])->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_replacement_limit' => 'nullable|numeric',
            'max_replacement_limit' => 'nullable|numeric',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->update([
            'name' => $request->name,
            'notes' => $request->notes ?? '',
            'min_replacement_limit' => (int) ($request->min_replacement_limit ?? 0),
            'max_replacement_limit' => (int) ($request->max_replacement_limit ?? 0),
        ]);

        return response()->json([
            'success' => true,
            'branch' => view('branches.single_row', ['branch' => $branch])->render()
        ]);
    }

    public function destroy($id)
    {
        Branch::findOrFail($id)->delete();
        return response()->json(['success' => true, 'id' => $id]);
    }

    public function multiDelete(Request $request)
    {
        Branch::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'ids' => $request->ids]);
    }

    public function search(Request $request)
    {
        $branches = Branch::where('name', 'like', "%{$request->q}%")->latest()->get();
        return view('branches.index_table', compact('branches'))->render();
    }
}
