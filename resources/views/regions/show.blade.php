@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تفاصيل المنطقة</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>اسم المنطقة:</strong> {{ $region->name }}</p>
            <p><strong>الفرع:</strong> {{ $region->branch->name ?? '-' }}</p>
            <p><strong>ملاحظات:</strong> {{ $region->notes ?? '-' }}</p>
        </div>
    </div>

    <a href="{{ route('regions.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('regions.edit', $region) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
