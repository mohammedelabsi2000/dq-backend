@extends('layouts.app')

@section('title', 'المستخدمين')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-users me-2"></i> إدارة المستخدمين</h2>
    <a href="#" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i>
        إضافة مستخدم جديد
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">بحث</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="اسم، بريد، جوال..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label">الجنس</label>
                <select name="gender" class="form-select">
                    <option value="">الكل</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">المسجد</label>
                <select name="mosque_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($mosques ?? [] as $mosque)
                        <option value="{{ $mosque->id }}" {{ request('mosque_id') == $mosque->id ? 'selected' : '' }}>
                            {{ $mosque->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">الحالة الاجتماعية</label>
                <select name="marital_status_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($maritalStatuses ?? [] as $status)
                        <option value="{{ $status->id }}" {{ request('marital_status_id') == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
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

<!-- Users Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>معلومات الاتصال</th>
                        <th>المسجد</th>
                        <th>الجنس/الحالة</th>
                        <th>تاريخ التسجيل</th>
                        <th width="150">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ $user->name }}&size=40&background=1e4a6b&color=fff"
                                     class="avatar me-2"
                                     style="width: 40px; height: 40px;">
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><i class="fas fa-phone ms-1 text-muted"></i> {{ $user->phone ?? '--' }}</div>
                            <div><i class="fab fa-whatsapp ms-1 text-success"></i> {{ $user->whatsapp ?? '--' }}</div>
                        </td>
                        <td>{{ $user->mosque->name ?? '--' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $user->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>
                            <br>
                            <small>{{ $user->maritalStatus->name ?? '--' }}</small>
                        </td>
                        <td>
                            {{ $user->created_at->format('Y/m/d') }}
                            <br>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'تأكيد حذف المستخدم', 'هل أنت متأكد من حذف المستخدم {{ $user->name }}؟');">
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
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا يوجد مستخدمين</h5>
                            <p class="text-muted">قم بإضافة مستخدم جديد للبدء</p>
                            <a href="#" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-1"></i>
                                إضافة مستخدم جديد
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($users) && $users->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Initialize Select2
    $(document).ready(function() {
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'اختر...',
            allowClear: true
        });
    });
</script>
@endpush
