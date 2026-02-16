@extends('layouts.app')

@section('content')
<h2>{{ $course->name }}</h2>

<p>المسار: {{ $course->track->name }}</p>
<p>اسم الكتاب: {{ $course->book_name }}</p>
<p>عدد الساعات: {{ $course->hours }}</p>
<p>الدرجة العظمى: {{ $course->max_score }}</p>

@endsection
