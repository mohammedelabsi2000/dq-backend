@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('courses.store') }}">
@csrf

<select name="track_id">
    @foreach($tracks as $track)
        <option value="{{ $track->id }}">{{ $track->name }}</option>
    @endforeach
</select>

<input type="text" name="name" placeholder="اسم الدورة">
<input type="text" name="book_name" placeholder="اسم كتاب الدورة">
<input type="number" name="hours" placeholder="عدد ساعات الدورة">
<input type="number" name="max_score" placeholder="الدرجة العظمى">

<button type="submit">حفظ</button>
</form>

@endsection
