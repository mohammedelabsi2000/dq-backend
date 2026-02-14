@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة الثوابت</h2>

    <a href="{{ route('constants.create') }}" class="btn btn-primary mb-3">إضافة ثابت جديد</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الثابت الرئيسي</th>
                <th>الحالة</th>
                <th>الملاحظات</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
            @forelse($constants as $constant)
            <tr>
                <td>{{ $constant->id }}</td>
                <td>{{ $constant->name }}</td>
                <td>{{ $constant->constantType->name ?? '-' }}</td>
                <td>{{ $constant->parent->name ?? '-' }}</td>
                <td>{{ $constant->is_active ? 'مفعل' : 'غير مفعل' }}</td>
                <td>{{ $constant->notes ?? '-' }}</td>
                <td>
                    <a href="{{ route('constants.show', $constant) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('constants.edit', $constant) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('constants.destroy', $constant) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">لا توجد ثوابت</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $constants->links() }}
</div>
@endsection
