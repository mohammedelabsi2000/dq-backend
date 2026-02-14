<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use App\Models\Region;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MosqueController extends Controller
{
    // ==================== MOSQUE CONTROLLER ====================

    /**
     * Display a listing of mosques
     */
    public function index(Request $request)
    public function index()
    {
        $query = Mosque::with(['region.branch', 'centers', 'users']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('region', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $mosques = $query->withCount(['centers', 'users'])->latest()->paginate(15);

        $branches = Branch::all();
        $mosques = Mosque::latest()->get();
        return view('mosques.index', compact('branches', 'mosques'));
    }

    /**
     * Show form to create a new mosque
     */
    public function create()
    {
        $regions = Region::with('branch')->get();
        return view('mosques.create', compact('regions'));
    }

    /**
     * Store a new mosque
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'notes' => 'nullable|string',
        ]);

        Mosque::create($request->all());

        return redirect()->route('mosques.index')
            ->with('success', 'تم إضافة المسجد بنجاح');
    }

    /**
     * Show mosque details
     */
    public function show(Mosque $mosque)
    {
        $mosque->load(['region.branch', 'centers', 'users']);
        return view('mosques.show', compact('mosque'));
    }

    /**
     * Show form to edit mosque
     */
    public function edit(Mosque $mosque)
    {
        $regions = Region::with('branch')->get();
        return view('mosques.edit', compact('mosque', 'regions'));
    }

    /**
     * Update mosque
     */
    public function update(Request $request, Mosque $mosque)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id'
        ]);

        $mosque->update($request->all());

        return redirect()->route('mosques.show', $mosque)
            ->with('success', 'تم تحديث المسجد بنجاح');
    }

    /**
     * Delete mosque
     */
    public function destroy(Mosque $mosque)
    {
        DB::beginTransaction();

        try {
            if ($mosque->centers()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف المسجد لوجود مراكز تابعة له');
            }

            if ($mosque->users()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف المسجد لوجود مستخدمين مرتبطين به');
            }

            $mosque->delete();

            DB::commit();

            return redirect()->route('mosques.index')
                ->with('success', 'تم حذف المسجد بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف المسجد: ' . $e->getMessage());
        }
    }

    /**
     * Get mosques by region (AJAX)
     */
    public function getByRegion($regionId)
    {
        $mosques = Mosque::where('region_id', $regionId)->get(['id', 'name']);
        return response()->json($mosques);
    }

    /**
     * Search mosques (AJAX)
     */
    public function search(Request $request)
    {
        Mosque::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }
}
