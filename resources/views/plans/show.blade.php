@extends('layouts.app')

@section('content')
<h2>تفاصيل الخطة</h2>

<p><strong>الاسم:</strong> {{ $plan->name }}</p>
<p><strong>الوزن:</strong> {{ $plan->weight }}</p>
<p><strong>المدة:</strong> {{ $plan->duration_in_days }} يوم</p>
<p><strong>فترة السماحية:</strong> {{ $plan->grace_period_days }} يوم</p>
<p><strong>الحالة:</strong> {{ $plan->is_active ? 'مفعلة' : 'غير مفعلة' }}</p>

<a href="{{ route('plans.index') }}">رجوع</a>

@endsection
