@extends('layouts.app')

@section('content')
<h2>إضافة مسار جديد</h2>

<form method="POST" action="{{ route('tracks.store') }}">
@csrf

<input type="text" name="name" placeholder="اسم المسار"><br><br>

<textarea name="description" placeholder="وصف المسار"></textarea><br><br>

<button type="submit">حفظ</button>
</form>

@endsection
