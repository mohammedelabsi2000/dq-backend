@extends('layouts.app')

@section('title', isset($mosque) ? 'تعديل مسجد' : 'إضافة مسجد جديد')

@section('content')
<div class="page-title d-flex justify-content-between align-items-center">
    <h2>
        <i class="fas fa-{{ isset($mosque) ? 'edit' : 'plus-circle' }} me-2"></i>
        {{ isset($mosque) ? 'تعديل مسجد' : 'إضافة مسجد جديد' }}
    </h2>
    <a href="{{ route('mosques.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        عودة للقائمة
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">معلومات المسجد</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ isset($mosque) ? route('mosques.update', $mosque->id) : route('mosques.store') }}" 
              method="POST">
            @csrf
            @if(isset($mosque))
                @method('PUT')
            @endif
            
            <div class="row">
                <!-- اسم المسجد -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">اسم المسجد <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $mosque->name ?? '') }}" 
                           required 
                           placeholder="أدخل اسم المسجد">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الفرع -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">الفرع <span class="text-danger">*</span></label>
                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">اختر الفرع</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" 
                                {{ old('branch_id', isset($mosque) && $mosque->region ? $mosque->region->branch_id : '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- المنطقة -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">المنطقة <span class="text-danger">*</span></label>
                    <select name="region_id" id="region_id" class="form-select @error('region_id') is-invalid @enderror" required>
                        <option value="">اختر المنطقة</option>
                        @if(isset($mosque) && $mosque->region_id)
                            <option value="{{ $mosque->region_id }}" selected>{{ $mosque->region->name }}</option>
                        @endif
                    </select>
                    @error('region_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- ملاحظات -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" 
                              class="form-control @error('notes') is-invalid @enderror" 
                              rows="3" 
                              placeholder="أدخل أي ملاحظات إضافية">{{ old('notes', $mosque->notes ?? '') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i>
                    {{ isset($mosque) ? 'تحديث المسجد' : 'حفظ المسجد' }}
                </button>
                <a href="{{ route('mosques.index') }}" class="btn btn-light px-4">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load regions when branch changes
        $('#branch_id').on('change', function() {
            var branchId = $(this).val();
            var $regionSelect = $('#region_id');
            
            if (branchId) {
                $.ajax({
                    url: '{{ route("api.branches.regions", "") }}/' + branchId,
                    type: 'GET',
                    beforeSend: function() {
                        $regionSelect.html('<option value="">جاري التحميل...</option>').prop('disabled', true);
                    },
                    success: function(data) {
                        $regionSelect.html('<option value="">اختر المنطقة</option>');
                        $.each(data, function(key, region) {
                            $regionSelect.append('<option value="' + region.id + '">' + region.name + '</option>');
                        });
                        $regionSelect.prop('disabled', false);
                        
                        // If editing, select the current region
                        @if(isset($mosque) && $mosque->region_id)
                            $regionSelect.val('{{ $mosque->region_id }}');
                        @endif
                    },
                    error: function() {
                        $regionSelect.html('<option value="">حدث خطأ في التحميل</option>');
                        $regionSelect.prop('disabled', false);
                    }
                });
            } else {
                $regionSelect.html('<option value="">اختر الفرع أولاً</option>').prop('disabled', true);
            }
        });
        
        // Trigger change on page load if editing
        @if(isset($mosque) && $mosque->region)
            $('#branch_id').trigger('change');
        @endif
    });
</script>
@endpush