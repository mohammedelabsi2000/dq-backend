@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">إضافة ثابت جديد</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('constants.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label>النوع</label>
            <select name="constant_type_id" class="form-control" required>
                <option value="">اختر النوع</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ old('constant_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>الثابت الرئيسي</label>
            <select name="parent_id" class="form-control">
                <option value="">لا يوجد</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label">مفعل</label>
        </div>

        <div class="mb-3">
            <label>الملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <button class="btn btn-success">حفظ</button>
        <a href="{{ route('constants.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
