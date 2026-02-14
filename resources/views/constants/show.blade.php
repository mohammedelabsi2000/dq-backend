@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تفاصيل الثابت</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $constant->name }}</p>
            <p><strong>النوع:</strong> {{ $constant->constantType->name ?? '-' }}</p>
            <p><strong>الثابت الرئيسي:</strong> {{ $constant->parent->name ?? '-' }}</p>
            <p><strong>الحالة:</strong> {{ $constant->is_active ? 'مفعل' : 'غير مفعل' }}</p>
            <p><strong>الملاحظات:</strong> {{ $constant->notes ?? '-' }}</p>
        </div>
    </div>

    <a href="{{ route('constants.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('constants.edit', $constant) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
