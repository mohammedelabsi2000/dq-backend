@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">عرض المركز</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $center->name }}</p>
            <p><strong>المسجد:</strong> {{ $center->mosque->name ?? '-' }}</p>
            <p><strong>ملاحظات:</strong> {{ $center->notes }}</p>
        </div>
    </div>

    <a href="{{ route('centers.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('centers.edit', $center) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
