@extends('layouts.app')

@section('content')
<h2>الدورات</h2>

<a href="{{ route('courses.create') }}">إضافة دورة</a>

@foreach($courses as $course)
    <div>
        <h4>{{ $course->name }}</h4>
        المسار: {{ $course->track->name ?? '-' }} |
        الساعات: {{ $course->hours }} |
        <a href="{{ route('courses.show',$course->id) }}">عرض</a>
        <a href="{{ route('courses.edit',$course->id) }}">تعديل</a>
    </div>
@endforeach

@endsection
