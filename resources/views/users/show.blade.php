@extends('layouts.app')

@section('title', 'عرض المستخدم - ' . $user->name)

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2>
        <i class="fas fa-user me-2"></i>
        عرض المستخدم: {{ $user->name }}
    </h2>
    <div>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>
            تعديل
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            عودة للقائمة
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-id-card me-2"></i>
                    المعلومات الشخصية
                </h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ $user->image_path ? asset('storage/'.$user->image_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=1e4a6b&color=fff' }}" 
                     class="rounded-circle mb-3" 
                     style="width: 120px; height: 120px; object-fit: cover;"
                     alt="{{ $user->name }}">
                
                <h4>{{ $user->name }}</h4>
                <p class="text-muted mb-2">
                    <i class="fas fa-envelope me-1"></i> {{ $user->email }}
                </p>
                <p class="text-muted mb-2">
                    <i class="fas fa-phone me-1"></i> {{ $user->phone ?? 'لا يوجد' }}
                </p>
                
                <hr>
                
                <div class="text-start">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%"><i class="fas fa-hashtag me-2"></i> الهوية:</th>
                            <td>{{ $user->identity ?? '--' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-venus-mars me-2"></i> الجنس:</th>
                            <td>
                                @if($user->gender == 'male')
                                    <span class="badge bg-info">ذكر</span>
                                @else
                                    <span class="badge bg-danger">أنثى</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar me-2"></i> تاريخ الميلاد:</th>
                            <td>{{ $user->dob ? $user->dob->format('Y/m/d') : '--' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-ring me-2"></i> الحالة الاجتماعية:</th>
                            <td>{{ $user->maritalStatus->name ?? '--' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-child me-2"></i> عدد الأبناء:</th>
                            <td>{{ $user->numChildren ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Location & Contact Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    الموقع والاتصال
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="fw-bold">المسجد:</h6>
                    <p>{{ $user->mosque->name ?? 'غير مرتبط' }}</p>
                    @if($user->mosque)
                        <small class="text-muted">
                            <i class="fas fa-sitemap me-1"></i> المنطقة: {{ $user->mosque->region->name ?? '--' }}<br>
                            <i class="fas fa-code-branch me-1"></i> الفرع: {{ $user->mosque->region->branch->name ?? '--' }}
                        </small>
                    @endif
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold">العنوان:</h6>
                    <p>{{ $user->location ?? 'لا يوجد عنوان' }}</p>
                </div>
                
                <div>
                    <h6 class="fw-bold">الواتساب:</h6>
                    <p>
                        @if($user->whatsapp)
                            <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" class="text-success">
                                <i class="fab fa-whatsapp fa-lg me-1"></i> {{ $user->whatsapp }}
                            </a>
                        @else
                            --
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Job Info Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i>
                    المعلومات الوظيفية
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">المسمى الوظيفي:</th>
                        <td>{{ $user->jobname ?? '--' }}</td>
                    </tr>
                    <tr>
                        <th>جهة العمل:</th>
                        <td>{{ $user->job_place ?? '--' }}</td>
                    </tr>
                    <tr>
                        <th>الراتب:</th>
                        <td>{{ $user->job_salary ? number_format($user->job_salary, 2).' ريال' : '--' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Academic & Courses Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>
                    المؤهلات الأكاديمية
                </h5>
            </div>
            <div class="card-body">
                @if($user->academicQualifications->count() > 0)
                    @foreach($user->academicQualifications as $qualification)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $qualification->academicDegree->name ?? '' }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $qualification->major->name ?? '' }}
                                @if($qualification->educational_institution)
                                    - {{ $qualification->educational_institution }}
                                @endif
                            </small>
                            <br>
                            <small class="text-primary">
                                تاريخ التخرج: {{ $qualification->date_graduate ? $qualification->date_graduate->format('Y/m/d') : '--' }}
                            </small>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        لا يوجد مؤهلات أكاديمية
                    </p>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-certificate me-2"></i>
                    الدورات
                </h5>
            </div>
            <div class="card-body">
                @if($user->personalCourses->count() > 0)
                    @foreach($user->personalCourses as $course)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $course->course_name }}</strong>
                            <br>
                            <span class="badge bg-info">{{ $course->type->name ?? '' }}</span>
                            @if($course->hours)
                                <span class="badge bg-secondary">{{ $course->hours }} ساعة</span>
                            @endif
                            <br>
                            <small class="text-muted">
                                {{ $course->provider ?? '' }}
                                @if($course->place)
                                    - {{ $course->place }}
                                @endif
                            </small>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        لا يوجد دورات
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- System Info Row -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات النظام
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">تاريخ التسجيل:</small>
                        <strong>{{ $user->created_at->format('Y/m/d h:i A') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">آخر تحديث:</small>
                        <strong>{{ $user->updated_at->format('Y/m/d h:i A') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">البريد الإلكتروني مفعل؟</small>
                        <strong>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">مفعل</span>
                                ({{ $user->email_verified_at->format('Y/m/d') }})
                            @else
                                <span class="badge bg-warning">غير مفعل</span>
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection