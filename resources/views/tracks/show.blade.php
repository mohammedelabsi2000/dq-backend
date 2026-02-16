@extends('layouts.app')

@section('content')
<h2>تفاصيل المسار</h2>

<p><strong>الاسم:</strong> {{ $track->name }}</p>
<p><strong>الوصف:</strong> {{ $track->description }}</p>

<a href="{{ route('tracks.index') }}">رجوع</a>

@endsection
