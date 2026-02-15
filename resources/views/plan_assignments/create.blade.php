@extends('layouts.app')

@section('content')
<h2>إسناد الطلاب للخطة المركبة: {{ $plan->name }}</h2>

<form method="GET" action="{{ route('plans.assign.create', $plan->id) }}">
    <h4>فلاتر البحث:</h4>
    فرع: <input type="text" name="branch_id" value="{{ request('branch_id') }}">
    المنطقة: <input type="text" name="region_id" value="{{ request('region_id') }}">
    العمر من: <input type="number" name="min_age" value="{{ request('min_age') }}">
    إلى: <input type="number" name="max_age" value="{{ request('max_age') }}">
    آخر إنجاز: <input type="number" name="last_memorized" value="{{ request('last_memorized') }}">
    بحث: <input type="text" name="search" value="{{ request('search') }}">
    <button type="submit">تطبيق الفلاتر</button>
</form>

<form method="POST" action="{{ route('plans.assign.store', $plan->id) }}">
@csrf

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>اختيار</th>
            <th>اسم الطالب</th>
            <th>الفرع</th>
            <th>المنطقة</th>
            <th>تاريخ الميلاد</th>
            <th>آخر إنجاز</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td><input type="checkbox" name="students[]" value="{{ $student->id }}"></td>
<td>{{ $student->full_name }}</td>
            <td>{{ $student->branch_id }}</td>
            <td>{{ $student->region_id }}</td>
            <td>{{ $student->birthdate }}</td>
            <td>{{ $student->last_memorized }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $students->withQueryString()->links() }}

<button type="submit">إسناد الطلاب المحددين</button>
</form>

@endsection
