@extends('layouts.app')

@section('content')
<h2>تعديل الخطة</h2>

<form method="POST" action="{{ route('plans.update',$plan->id) }}">
@csrf
@method('PUT')

<input type="text" name="name" value="{{ $plan->name }}"><br><br>

<input type="number" name="weight" value="{{ $plan->weight }}"><br><br>

<input type="number" name="duration_in_days" value="{{ $plan->duration_in_days }}"><br><br>

<input type="number" name="grace_period_days" value="{{ $plan->grace_period_days }}"><br><br>

<label>
    <input type="checkbox" name="is_active" value="1"
        {{ $plan->is_active ? 'checked' : '' }}>
    مفعلة
</label>

<br><br>

<button type="submit">تحديث</button>
</form>

@endsection
