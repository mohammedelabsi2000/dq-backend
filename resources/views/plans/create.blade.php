@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">إضافة خطة جديدة</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('plans.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label>النوع</label>
            <select name="type_id" class="form-control" required>
                <option value="">اختر النوع</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>الفئة المستهدفة</label>
            <select name="target_group_id" class="form-control" required>
                <option value="">اختر الفئة</option>
                @foreach($targetGroups as $group)
                    <option value="{{ $group->id }}" {{ old('target_group_id') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>عدد المستويات</label>
            <input type="number" name="level_numbers" class="form-control" value="{{ old('level_numbers') }}">
        </div>

        <div class="mb-3">
            <label>الوصف</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label>الملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <button class="btn btn-success">حفظ</button>
        <a href="{{ route('plans.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
