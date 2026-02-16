<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mosque;
use App\Models\Center;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ==================== DASHBOARD CONTROLLER ====================

    /**
     * Display dashboard
     */
    public function index()
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();

        $totalMosques = Mosque::count();
        $newMosques = Mosque::whereMonth('created_at', now()->month)->count();

        $totalCenters = Center::count();
        $activeCenters = Center::whereHas('mosque', function ($q) {
            $q->whereHas('region', function ($r) {
                $r->whereHas('branch');
            });
        })->count();

        $totalPlans = Plan::count();
        $activePlans = Plan::count();

        $recentUsers = User::latest()->take(5)->get();
        $recentMosques = Mosque::with('region.branch')->latest()->take(5)->get();

        $usersByGender = User::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->get();

        $usersByMonth = User::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard', compact(
            'totalUsers',
            'newUsersThisMonth',
            'totalMosques',
            'newMosques',
            'totalCenters',
            'activeCenters',
            'totalPlans',
            'activePlans',
            'recentUsers',
            'recentMosques',
            'usersByGender',
            'usersByMonth'
        ));
    }
}
