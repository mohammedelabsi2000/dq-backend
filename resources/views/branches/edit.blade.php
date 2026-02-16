@extends('layouts.app')

@section('content')
<div class="container">

    <h2>تعديل الفرع</h2>

    <form action="{{ route('branches.update', $branch) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم الفرع</label>
            <input type="text" name="name" value="{{ $branch->name }}" class="form-control" required>
        </div>

        <button class="btn btn-primary">تحديث</button>

        <div class="mb-3">
            <label>الحد الأدنى للحذف</label>
            <input type="number" name="min_replacement_limit" class="form-control" value="{{ old('min_replacement_limit', $branch->min_replacement_limit) }}" required>
        </div>
        <div class="mb-3">
            <label>الحد الأدنى للاستبدال</label>
            <input type="number" name="max_replacement_limit" class="form-control" value="{{ old('max_replacement_limit', $branch->max_replacement_limit) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">رجوع</a>
    </form>

</div>
@endsection
