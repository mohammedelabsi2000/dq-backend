@extends('layouts.app')

@section('title', 'الخطط الدراسية')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-layer-group me-2"></i> إدارة الخطط الدراسية</h2>
    <a href="{{ route('plans.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i>
        إضافة خطة جديدة
    </a>
</div>

<div class="row g-4">
    @forelse($plans ?? [] as $plan)
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $plan->name }}</h5>
                <span class="badge bg-{{ $plan->is_active ? 'success' : 'danger' }}">
                    {{ $plan->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-primary">{{ $plan->type->name ?? 'نوع الخطة' }}</span>
                    <span class="badge bg-info">{{ $plan->targetGroup->name ?? 'الفئة المستهدفة' }}</span>
                </div>
                
                <p class="text-muted small">{{ $plan->description ?? 'لا يوجد وصف' }}</p>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">عدد المستويات</small>
                        <h4 class="mb-0">{{ $plan->level_numbers ?? 0 }}</h4>
                    </div>
                    <div>
                        <small class="text-muted">المراكز المرتبطة</small>
                        <h4 class="mb-0">{{ $plan->centers_count ?? 0 }}</h4>
                    </div>
                    <div>
                        <small class="text-muted">الطلاب</small>
                        <h4 class="mb-0">{{ $plan->students_count ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $plan->created_at->format('Y/m/d') }}
                    </small>
                    <div class="btn-group">
                        <a href="{{ route('plans.show', $plan->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('plans.edit', $plan->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('plans.levels', $plan->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-level-up-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">لا يوجد خطط دراسية</h5>
                <p class="text-muted">قم بإضافة خطة دراسية جديدة للبدء</p>
                <a href="{{ route('plans.create') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-plus-circle me-1"></i>
                    إضافة خطة جديدة
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>