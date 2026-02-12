@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تفاصيل الخطة</h2>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $plan->name }}</p>
            <p><strong>النوع:</strong> {{ $plan->type->name ?? '-' }}</p>
            <p><strong>الفئة المستهدفة:</strong> {{ $plan->targetGroup->name ?? '-' }}</p>
            <p><strong>عدد المستويات:</strong> {{ $plan->level_numbers ?? '-' }}</p>
            <p><strong>الوصف:</strong> {{ $plan->description ?? '-' }}</p>
            <p><strong>الملاحظات:</strong> {{ $plan->notes ?? '-' }}</p>
        </div>
    </div>

    <a href="{{ route('plans.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-warning">تعديل</a>
</div>
@endsection
