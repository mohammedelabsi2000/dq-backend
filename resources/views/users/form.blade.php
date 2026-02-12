@extends('layouts.app')

@section('title', isset($user) ? 'تعديل مستخدم' : 'إضافة مستخدم جديد')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2>
        <i class="fas fa-{{ isset($user) ? 'edit' : 'plus-circle' }} me-2"></i>
        {{ isset($user) ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' }}
    </h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        عودة للقائمة
    </a>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
                    <i class="fas fa-user me-1"></i> المعلومات الأساسية
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                    <i class="fas fa-address-book me-1"></i> معلومات الاتصال
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab">
                    <i class="fas fa-briefcase me-1"></i> المعلومات الوظيفية
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                    <i class="fas fa-graduation-cap me-1"></i> المؤهلات والدورات
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" 
              method="POST" 
              enctype="multipart/form-data">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif
            
            <div class="tab-content" id="userTabsContent">
                <!-- Basic Information Tab -->
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                            <input type="text" name="fName" class="form-control @error('fName') is-invalid @enderror" 
                                   value="{{ old('fName', $user->fName ?? '') }}" required>
                            @error('fName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">الاسم الثاني</label>
                            <input type="text" name="sName" class="form-control @error('sName') is-invalid @enderror" 
                                   value="{{ old('sName', $user->sName ?? '') }}">
                            @error('sName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">الاسم الثالث</label>
                            <input type="text" name="thName" class="form-control @error('thName') is-invalid @enderror" 
                                   value="{{ old('thName', $user->thName ?? '') }}">
                            @error('thName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">العائلة</label>
                            <input type="text" name="family" class="form-control @error('family') is-invalid @enderror" 
                                   value="{{ old('family', $user->family ?? '') }}">
                            @error('family')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">اللقب</label>
                            <select name="prefix_name_id" class="form-select @error('prefix_name_id') is-invalid @enderror">
                                <option value="">اختر اللقب</option>
                                @foreach($prefixes ?? [] as $prefix)
                                    <option value="{{ $prefix->id }}" {{ old('prefix_name_id', $user->prefix_name_id ?? '') == $prefix->id ? 'selected' : '' }}>
                                        {{ $prefix->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prefix_name_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">رقم الهوية</label>
                            <input type="text" name="identity" class="form-control @error('identity') is-invalid @enderror" 
                                   value="{{ old('identity', $user->identity ?? '') }}">
                            @error('identity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if(!isset($user))
                        <div class="col-md-6 mb-3">
                            <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        @endif
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ الميلاد</label>
                            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                                   value="{{ old('dob', isset($user) && $user->dob ? $user->dob->format('Y-m-d') : '') }}">
                            @error('dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الجنس <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="">اختر</option>
                                <option value="male" {{ old('gender', $user->gender ?? '') == 'male' ? 'selected' : '' }}>ذكر</option>
                                <option value="female" {{ old('gender', $user->gender ?? '') == 'female' ? 'selected' : '' }}>أنثى</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحالة الاجتماعية</label>
                            <select name="marital_status_id" class="form-select @error('marital_status_id') is-invalid @enderror">
                                <option value="">اختر</option>
                                @foreach($maritalStatuses ?? [] as $status)
                                    <option value="{{ $status->id }}" {{ old('marital_status_id', $user->marital_status_id ?? '') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marital_status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">عدد الأبناء</label>
                            <input type="number" name="numChildren" class="form-control @error('numChildren') is-invalid @enderror" 
                                   value="{{ old('numChildren', $user->numChildren ?? 0) }}" min="0">
                            @error('numChildren')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Tab -->
                <div class="tab-pane fade" id="contact" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">المسجد</label>
                            <select name="mosque_id" class="form-select @error('mosque_id') is-invalid @enderror">
                                <option value="">اختر المسجد</option>
                                @foreach($mosques ?? [] as $mosque)
                                    <option value="{{ $mosque->id }}" {{ old('mosque_id', $user->mosque_id ?? '') == $mosque->id ? 'selected' : '' }}>
                                        {{ $mosque->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mosque_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" 
                                   value="{{ old('location', $user->location ?? '') }}" placeholder="المنطقة، المدينة، الحي">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">رقم الجوال</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone ?? '') }}" placeholder="05xxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">رقم الواتساب</label>
                            <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" 
                                   value="{{ old('whatsapp', $user->whatsapp ?? '') }}" placeholder="05xxxxxxxx">
                            @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الصورة الشخصية</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Job Information Tab -->
                <div class="tab-pane fade" id="job" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">المسمى الوظيفي</label>
                            <input type="text" name="jobname" class="form-control @error('jobname') is-invalid @enderror" 
                                   value="{{ old('jobname', $user->jobname ?? '') }}">
                            @error('jobname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">جهة العمل</label>
                            <input type="text" name="job_place" class="form-control @error('job_place') is-invalid @enderror" 
                                   value="{{ old('job_place', $user->job_place ?? '') }}">
                            @error('job_place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الراتب</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="job_salary" class="form-control @error('job_salary') is-invalid @enderror" 
                                       value="{{ old('job_salary', $user->job_salary ?? '') }}">
                                <span class="input-group-text">ريال</span>
                                @error('job_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Tab -->
                <div class="tab-pane fade" id="academic" role="tabpanel">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">المؤهلات الأكاديمية</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Dynamic qualifications will be added here -->
                                    <div id="qualifications-container">
                                        @if(isset($user) && $user->academicQualifications->count() > 0)
                                            @foreach($user->academicQualifications as $index => $qualification)
                                            <div class="row qualification-item mb-2">
                                                <div class="col-md-3">
                                                    <select name="qualifications[{{ $index }}][academic_degree_id]" class="form-select">
                                                        <option value="">الدرجة العلمية</option>
                                                        @foreach($academicDegrees ?? [] as $degree)
                                                            <option value="{{ $degree->id }}" {{ $qualification->academic_degree_id == $degree->id ? 'selected' : '' }}>
                                                                {{ $degree->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select name="qualifications[{{ $index }}][major_id]" class="form-select">
                                                        <option value="">التخصص</option>
                                                        @foreach($majors ?? [] as $major)
                                                            <option value="{{ $major->id }}" {{ $qualification->major_id == $major->id ? 'selected' : '' }}>
                                                                {{ $major->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="date" name="qualifications[{{ $index }}][date_graduate]" class="form-control" 
                                                           value="{{ $qualification->date_graduate->format('Y-m-d') ?? '' }}" placeholder="تاريخ التخرج">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" name="qualifications[{{ $index }}][educational_institution]" class="form-control" 
                                                           value="{{ $qualification->educational_institution }}" placeholder="المؤسسة التعليمية">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" id="addQualification">
                                        <i class="fas fa-plus-circle"></i> إضافة مؤهل
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">الدورات</h6>
                                </div>
                                <div class="card-body">
                                    <div id="courses-container">
                                        @if(isset($user) && $user->personalCourses->count() > 0)
                                            @foreach($user->personalCourses as $index => $course)
                                            <div class="row course-item mb-2">
                                                <div class="col-md-3">
                                                    <input type="text" name="courses[{{ $index }}][course_name]" class="form-control" 
                                                           value="{{ $course->course_name }}" placeholder="اسم الدورة">
                                                </div>
                                                <div class="col-md-2">
                                                    <select name="courses[{{ $index }}][type_id]" class="form-select">
                                                        <option value="">نوع الدورة</option>
                                                        @foreach($courseTypes ?? [] as $type)
                                                            <option value="{{ $type->id }}" {{ $course->type_id == $type->id ? 'selected' : '' }}>
                                                                {{ $type->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" name="courses[{{ $index }}][hours]" class="form-control" 
                                                           value="{{ $course->hours }}" placeholder="عدد الساعات">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" name="courses[{{ $index }}][provider]" class="form-control" 
                                                           value="{{ $course->provider }}" placeholder="الجهة المقدمة">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" name="courses[{{ $index }}][place]" class="form-control" 
                                                           value="{{ $course->place }}" placeholder="مكان الانعقاد">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" id="addCourse">
                                        <i class="fas fa-plus-circle"></i> إضافة دورة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i>
                    {{ isset($user) ? 'تحديث البيانات' : 'حفظ المستخدم' }}
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-light px-4">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let qualificationIndex = {{ isset($user) ? $user->academicQualifications->count() : 0 }};
    let courseIndex = {{ isset($user) ? $user->personalCourses->count() : 0 }};
    
    $(document).ready(function() {
        // Add Qualification
        $('#addQualification').click(function() {
            let html = `
                <div class="row qualification-item mb-2">
                    <div class="col-md-3">
                        <select name="qualifications[${qualificationIndex}][academic_degree_id]" class="form-select">
                            <option value="">الدرجة العلمية</option>
                            @foreach($academicDegrees ?? [] as $degree)
                                <option value="{{ $degree->id }}">{{ $degree->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="qualifications[${qualificationIndex}][major_id]" class="form-select">
                            <option value="">التخصص</option>
                            @foreach($majors ?? [] as $major)
                                <option value="{{ $major->id }}">{{ $major->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="qualifications[${qualificationIndex}][date_graduate]" class="form-control" placeholder="تاريخ التخرج">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="qualifications[${qualificationIndex}][educational_institution]" class="form-control" placeholder="المؤسسة التعليمية">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#qualifications-container').append(html);
            qualificationIndex++;
        });
        
        // Add Course
        $('#addCourse').click(function() {
            let html = `
                <div class="row course-item mb-2">
                    <div class="col-md-3">
                        <input type="text" name="courses[${courseIndex}][course_name]" class="form-control" placeholder="اسم الدورة">
                    </div>
                    <div class="col-md-2">
                        <select name="courses[${courseIndex}][type_id]" class="form-select">
                            <option value="">نوع الدورة</option>
                            @foreach($courseTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="courses[${courseIndex}][hours]" class="form-control" placeholder="عدد الساعات">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="courses[${courseIndex}][provider]" class="form-control" placeholder="الجهة المقدمة">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="courses[${courseIndex}][place]" class="form-control" placeholder="مكان الانعقاد">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#courses-container').append(html);
            courseIndex++;
        });
        
        // Remove item
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.row').remove();
        });
        
        // Initialize Select2
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush