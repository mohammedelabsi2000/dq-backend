@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تعديل المنطقة</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('regions.update', $region) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم المنطقة</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $region->name) }}" required>
        </div>

        <div class="mb-3">
            <label>الفرع</label>
            <select name="branch_id" class="form-control" required>
                <option value="">اختر الفرع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $region->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $region->notes) }}</textarea>
        </div>

        <button class="btn btn-success">تحديث</button>
        <a href="{{ route('regions.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
