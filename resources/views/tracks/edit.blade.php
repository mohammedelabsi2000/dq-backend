@extends('layouts.app')

@section('content')
<h2>تعديل المسار</h2>

<form method="POST" action="{{ route('tracks.update',$track->id) }}">
@csrf
@method('PUT')

<input type="text" name="name" value="{{ $track->name }}"><br><br>

<textarea name="description">{{ $track->description }}</textarea><br><br>

<button type="submit">تحديث</button>
</form>

@endsection
