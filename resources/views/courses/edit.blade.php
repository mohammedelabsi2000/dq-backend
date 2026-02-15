@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('courses.update',$course->id) }}">
@csrf
@method('PUT')

<select name="track_id">
    @foreach($tracks as $track)
        <option value="{{ $track->id }}"
            {{ $track->id == $course->track_id ? 'selected' : '' }}>
            {{ $track->name }}
        </option>
    @endforeach
</select>

<input type="text" name="name" value="{{ $course->name }}">
<input type="text" name="book_name" value="{{ $course->book_name }}">
<input type="number" name="hours" value="{{ $course->hours }}">
<input type="number" name="max_score" value="{{ $course->max_score }}">

<button type="submit">تحديث</button>
</form>
n
