<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MosqueController extends Controller
{
    // ==================== MOSQUE CONTROLLER ====================

    /**
     * Display mosques list
     */
    public function index(Request $request)
    {
        $query = Mosque::with(['region.branch', 'centers', 'users']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('region', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $mosques = $query->withCount(['centers', 'users'])->latest()->paginate(15);

        $branches = Branch::all();
        $regions = Region::all();

        return view('mosques.index', compact('mosques', 'branches', 'regions'));
    }

    /**
     * Show create mosque form
     */
    public function create()
    {
        $regions = Region::with('branch')->get();
        return view('mosques.form', compact('regions'));
    }

    /**
     * Store new mosque
     */
    public function storeMosque(Request $request)
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
    public function showMosque($id)
    {
        $mosque = Mosque::with(['region.branch', 'centers', 'users'])->findOrFail($id);
        return view('mosques.show', compact('mosque'));
    }

    /**
     * Show edit mosque form
     */
    public function editMosque($id)
    {
        $mosque = Mosque::findOrFail($id);
        $regions = Region::with('branch')->get();
        return view('mosques.form', compact('mosque', 'regions'));
    }

    /**
     * Update mosque
     */
    public function updateMosque(Request $request, $id)
    {
        $mosque = Mosque::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'notes' => 'nullable|string',
        ]);

        $mosque->update($request->all());

        return redirect()->route('mosques.show', $mosque->id)
            ->with('success', 'تم تحديث المسجد بنجاح');
    }

    /**
     * Delete mosque
     */
    public function destroyMosque($id)
    {
        $mosque = Mosque::findOrFail($id);

        DB::beginTransaction();

        try {
            // Check if mosque has related data
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
    public function getMosquesByRegion($regionId)
    {
        $mosques = Mosque::where('region_id', $regionId)->get(['id', 'name']);
        return response()->json($mosques);
    }

    /**
     * Search mosques (AJAX)
     */
    public function searchMosques(Request $request)
    {
        $query = $request->get('q');
        $mosques = Mosque::where('name', 'like', "%{$query}%")->limit(10)->get(['id', 'name']);
        return response()->json($mosques);
    }
}
