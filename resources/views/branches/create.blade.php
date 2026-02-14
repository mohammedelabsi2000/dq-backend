@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إضافة فرع جديد</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('branches.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
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
