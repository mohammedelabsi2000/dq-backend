@extends('layouts.app')

@section('content')
<h2>إضافة خطة جديدة</h2>

<form method="POST" action="{{ route('plans.store') }}">
@csrf

<input type="text" name="name" placeholder="اسم الخطة"><br><br>

<input type="number" name="weight" placeholder="وزن الخطة"><br><br>

<input type="number" name="duration_in_days" placeholder="مدة الخطة بالأيام"><br><br>

<input type="number" name="grace_period_days" placeholder="فترة السماحية بالأيام"><br><br>

<label>
    <input type="checkbox" name="is_active" value="1" checked>
    مفعلة
</label>

<br><br>

<button type="submit">حفظ</button>
</form>

@endsection
