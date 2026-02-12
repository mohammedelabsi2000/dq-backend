@extends('layouts.app')

@section('title', 'المساجد')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-mosque me-2"></i> إدارة المساجد</h2>
    <a href="{{ route('mosques.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i>
        إضافة مسجد جديد
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('mosques.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">بحث</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="اسم المسجد..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">الفرع</label>
                <select name="branch_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">المنطقة</label>
                <select name="region_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($regions ?? [] as $region)
                        <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> تصفية
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Mosques Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المسجد</th>
                        <th>المنطقة</th>
                        <th>الفرع</th>
                        <th>عدد المراكز</th>
                        <th>عدد المستخدمين</th>
                        <th>تاريخ الإضافة</th>
                        <th width="150">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mosques ?? [] as $mosque)
                    <tr>
                        <td>{{ $mosque->id }}</td>
                        <td>
                            <strong>{{ $mosque->name }}</strong>
                            @if($mosque->notes)
                                <br>
                                <small class="text-muted">{{ Str::limit($mosque->notes, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $mosque->region->name ?? '--' }}</td>
                        <td>{{ $mosque->region->branch->name ?? '--' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $mosque->centers_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $mosque->users_count ?? 0 }}</span>
                        </td>
                        <td>
                            {{ $mosque->created_at->format('Y/m/d') }}
                            <br>
                            <small class="text-muted">{{ $mosque->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('mosques.show', $mosque->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('mosques.edit', $mosque->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mosques.destroy', $mosque->id) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'تأكيد حذف المسجد', 'هل أنت متأكد من حذف المسجد {{ $mosque->name }}؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-mosque fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا يوجد مساجد</h5>
                            <p class="text-muted">قم بإضافة مسجد جديد للبدء</p>
                            <a href="{{ route('mosques.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-1"></i>
                                إضافة مسجد جديد
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($mosques) && $mosques->hasPages())
    <div class="card-footer">
        {{ $mosques->links() }}
    </div>
    @endif
</div>
@endsection