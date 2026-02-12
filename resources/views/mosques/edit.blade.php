@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تعديل المسجد</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('mosques.update', $mosque) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم المسجد</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $mosque->name) }}" required>
        </div>

        <div class="mb-3">
            <label>المنطقة</label>
            <select name="region_id" class="form-control" required>
                <option value="">اختر المنطقة</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ old('region_id', $mosque->region_id) == $region->id ? 'selected' : '' }}>
                        {{ $region->name }} ({{ $region->branch->name ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $mosque->notes) }}</textarea>
        </div>

        <button class="btn btn-success">تحديث</button>
        <a href="{{ route('mosques.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
