@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل الفرع</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('branches.update', $branch->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
        </div>
        <div class="mb-3">
            <label>الحد الأدنى للحذف</label>
            <input type="number" name="min_delete" class="form-control" value="{{ old('min_delete', $branch->min_delete) }}" required>
        </div>
        <div class="mb-3">
            <label>الحد الأدنى للاستبدال</label>
            <input type="number" name="min_replace" class="form-control" value="{{ old('min_replace', $branch->min_replace) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
