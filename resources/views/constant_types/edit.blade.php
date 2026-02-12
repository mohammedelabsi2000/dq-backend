@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">{{ isset($constantType) ? 'تعديل نوع الثابت' : 'إضافة نوع جديد' }}</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ isset($constantType) ? route('constant-types.update', $constantType) : route('constant-types.store') }}" method="POST">
        @csrf
        @isset($constantType) @method('PUT') @endisset

        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $constantType->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label>الوصف</label>
            <input type="text" name="description" class="form-control" value="{{ old('description', $constantType->description ?? '') }}">
        </div>

        <div class="mb-3">
            <label>الملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $constantType->notes ?? '') }}</textarea>
        </div>

        <button class="btn btn-success">{{ isset($constantType) ? 'تحديث' : 'حفظ' }}</button>
        <a href="{{ route('constant-types.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
