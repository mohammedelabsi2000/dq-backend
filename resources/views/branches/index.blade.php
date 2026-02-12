@extends('layouts.app')

@section('content')
<div class="container">
    <h1>الفروع</h1>
    <a href="{{ route('branches.create') }}" class="btn btn-success mb-3">إضافة فرع جديد</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الحد الأدنى للحذف</th>
                <th>الحد الأدنى للاستبدال</th>
                <th>العمليات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->min_replacement_limit }}</td>
                    <td>{{ $branch->max_replacement_limit }}</td>
                    <td>
                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-primary btn-sm">تعديل</a>

                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
