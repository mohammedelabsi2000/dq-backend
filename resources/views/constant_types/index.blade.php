@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة أنواع الثوابت</h2>
    <a href="{{ route('constant-types.create') }}" class="btn btn-primary mb-3">إضافة نوع جديد</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>الملاحظات</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
            @forelse($types as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td>{{ $type->name }}</td>
                    <td>{{ $type->description ?? '-' }}</td>
                    <td>{{ $type->notes ?? '-' }}</td>
                    <td>
                        <a href="{{ route('constant-types.show', $type) }}" class="btn btn-info btn-sm">عرض</a>
                        <a href="{{ route('constant-types.edit', $type) }}" class="btn btn-warning btn-sm">تعديل</a>
                        <form action="{{ route('constant-types.destroy', $type) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">لا توجد أنواع ثوابت</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $types->links() }}
</div>
@endsection
