@extends('layouts.dashboard')

@section('title', 'الرئيسية - لوحة التحكم')
@section('page-title', 'الرئيسية')

@section('content')
    <!-- Stats Cards -->
    @include('dashboard.components.stats-cards')
    
    <!-- Charts -->
    <!-- <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">تحليل المبيعات</h3>
                <select style="padding: 5px 10px; border: 1px solid #e3e6f0; border-radius: 5px;">
                    <option>آخر 7 أيام</option>
                    <option>آخر 30 يوم</option>
                    <option>آخر 12 شهر</option>
                </select>
            </div>
            <canvas id="salesChart" height="300"></canvas>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">توزيع الفئات</h3>
            </div>
            <canvas id="categoryChart" height="300"></canvas>
        </div>
    </div> -->
    
    <!-- Recent Orders -->
    @include('dashboard.components.recent-orders')
@endsection

@push('scripts')
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
            datasets: [{
                label: 'المبيعات',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                pointBackgroundColor: '#4e73df',
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
                    beginAtZero: true,
                    grid: {
                        color: '#e3e6f0'
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['إلكترونيات', 'ملابس', 'كتب', 'أجهزة منزلية', 'أخرى'],
            datasets: [{
                data: [35, 25, 15, 20, 5],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#858796'],
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
</script>
@endpush