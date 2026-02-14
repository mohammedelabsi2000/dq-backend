@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تفاصيل المسجد</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>اسم المسجد:</strong> {{ $mosque->name }}</p>
            <p><strong>الفرع:</strong> {{ $mosque->region->branch->name ?? '-' }}</p>
            <p><strong>المنطقة:</strong> {{ $mosque->region->name ?? '-' }}</p>
            <p><strong>ملاحظات:</strong> {{ $mosque->notes ?? '-' }}</p>
            <p><strong>عدد المراكز:</strong> {{ $mosque->centers()->count() }}</p>
            <p><strong>عدد المستخدمين:</strong> {{ $mosque->users()->count() }}</p>
        </div>
    </div>

    <a href="{{ route('mosques.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('mosques.edit', $mosque) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
