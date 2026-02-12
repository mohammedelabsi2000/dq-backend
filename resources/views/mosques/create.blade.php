@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">إضافة مسجد جديد</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('mosques.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>اسم المسجد</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label>المنطقة</label>
            <select name="region_id" class="form-control" required>
                <option value="">اختر المنطقة</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                        {{ $region->name }} ({{ $region->branch->name ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <button class="btn btn-success">حفظ</button>
        <a href="{{ route('mosques.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
