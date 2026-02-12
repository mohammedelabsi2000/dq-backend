@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم</h2>
    <div>
        <span class="text-muted">{{ now()->format('Y/m/d') }}</span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary ms-3">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h6 class="text-muted mb-1">إجمالي المستخدمين</h6>
                <h3 class="mb-0">{{ $totalUsers ?? 0 }}</h3>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +{{ $newUsersThisMonth ?? 0 }} هذا الشهر
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-success bg-opacity-10 text-success ms-3">
                <i class="fas fa-mosque"></i>
            </div>
            <div>
                <h6 class="text-muted mb-1">المساجد</h6>
                <h3 class="mb-0">{{ $totalMosques ?? 0 }}</h3>
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i> +{{ $newMosques ?? 0 }} هذا الشهر
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-info bg-opacity-10 text-info ms-3">
                <i class="fas fa-school"></i>
            </div>
            <div>
                <h6 class="text-muted mb-1">المراكز</h6>
                <h3 class="mb-0">{{ $totalCenters ?? 0 }}</h3>
                <small class="text-muted">+{{ $activeCenters ?? 0 }} نشط</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning ms-3">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h6 class="text-muted mb-1">الخطط الدراسية</h6>
                <h3 class="mb-0">{{ $totalPlans ?? 0 }}</h3>
                <small class="text-muted">{{ $activePlans ?? 0 }} خطة نشطة</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">إحصائيات المستخدمين</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        هذا الشهر
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">هذا الأسبوع</a></li>
                        <li><a class="dropdown-item" href="#">هذا الشهر</a></li>
                        <li><a class="dropdown-item" href="#">هذا العام</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="usersChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">توزيع المستخدمين حسب الجنس</h5>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="genderChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Records -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">أحدث المستخدمين</h5>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
                    عرض الكل <i class="fas fa-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الجوال</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers ?? [] as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ $user->name }}&size=30" class="avatar me-2" style="width: 30px; height: 30px;">
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '--' }}</td>
                                <td>
                                    <span class="badge bg-success">نشط</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">لا يوجد مستخدمين بعد</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">أحدث المساجد</h5>
                <a href="{{ route('mosques.index') }}" class="btn btn-sm btn-primary">
                    عرض الكل <i class="fas fa-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>اسم المسجد</th>
                                <th>المنطقة</th>
                                <th>الفرع</th>
                                <th>تاريخ الإضافة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMosques ?? [] as $mosque)
                            <tr>
                                <td>{{ $mosque->name }}</td>
                                <td>{{ $mosque->region->name ?? '--' }}</td>
                                <td>{{ $mosque->region->branch->name ?? '--' }}</td>
                                <td>{{ $mosque->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-mosque fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">لا يوجد مساجد بعد</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Users Chart
        const usersCtx = document.getElementById('usersChart')?.getContext('2d');
        if (usersCtx) {
            new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                    datasets: [{
                        label: 'المستخدمين',
                        data: [65, 78, 90, 115, 132, 158],
                        borderColor: '#1e4a6b',
                        backgroundColor: 'rgba(30, 74, 107, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Gender Chart
        const genderCtx = document.getElementById('genderChart')?.getContext('2d');
        if (genderCtx) {
            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['ذكور', 'إناث'],
                    datasets: [{
                        data: [65, 35],
                        backgroundColor: ['#3498db', '#e84393'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endsection