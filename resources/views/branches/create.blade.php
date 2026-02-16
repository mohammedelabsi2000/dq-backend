@extends('layouts.app')

@section('content')
<div class="container">

    <h2>إضافة فرع</h2>

    <form action="{{ route('branches.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>اسم الفرع</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button class="btn btn-success">حفظ</button>

        <div class="mb-3">
            <label>الحد الأدنى للحذف</label>
            <input type="number" name="min_replacement_limit" class="form-control" value="{{ old('min_replacement_limit', 0) }}" required>
        </div>
        <div class="mb-3">
            <label>الحد الأدنى للاستبدال</label>
            <input type="number" name="max_replacement_limit" class="form-control" value="{{ old('max_replacement_limit', 0) }}" required>
        </div>
        <button type="submit" class="btn btn-success">حفظ</button>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">رجوع</a>
    </form>

</div>
@endsection
