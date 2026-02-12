@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تفاصيل نوع الثابت</h2>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $constantType->name }}</p>
            <p><strong>الوصف:</strong> {{ $constantType->description ?? '-' }}</p>
            <p><strong>الملاحظات:</strong> {{ $constantType->notes ?? '-' }}</p>
        </div>
    </div>
    <a href="{{ route('constant-types.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('constant-types.edit', $constantType) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
