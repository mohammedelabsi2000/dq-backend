@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">تعديل مركز</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('centers.update', $center) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم المركز</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $center->name) }}" required>
        </div>

        <div class="mb-3">
            <label>المسجد</label>
            <select name="mosque_id" class="form-control" required>
                <option value="">اختر المسجد</option>
                @foreach($mosques as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('mosque_id', $center->mosque_id) == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $center->notes) }}</textarea>
        </div>

        <button class="btn btn-success">تحديث</button>
        <a href="{{ route('centers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
