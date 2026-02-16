@extends('layouts.app')

@section('content')
<h2>طلاب الخطة: {{ $plan->name }}</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>اسم الطالب</th>
            <th>الفرع</th>
            <th>المنطقة</th>
            <th>تاريخ الميلاد</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $assignment)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $assignment->student->name ?? 'محذوف' }}</td>
            <td>{{ $assignment->student->branch_id ?? '' }}</td>
            <td>{{ $assignment->student->region_id ?? '' }}</td>
            <td>{{ $assignment->student->dob ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $students->links() }}

<a href="{{ route('plans.setup.show', $plan->id) }}" class="btn btn-light">رجوع للخطة</a>
@endsection
