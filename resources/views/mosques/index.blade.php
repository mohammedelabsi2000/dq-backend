@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة المساجد</h2>

    <a href="{{ route('mosques.create') }}" class="btn btn-primary mb-3">إضافة مسجد جديد</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الفرع</th>
                <th>المنطقة</th>
                <th>عدد المراكز</th>
                <th>عدد المستخدمين</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
        @forelse($mosques as $mosque)
            <tr>
                <td>{{ $mosque->id }}</td>
                <td>{{ $mosque->name }}</td>
                <td>{{ $mosque->region->branch->name ?? '-' }}</td>
                <td>{{ $mosque->region->name ?? '-' }}</td>
                <td>{{ $mosque->centers_count }}</td>
                <td>{{ $mosque->users_count }}</td>
                <td>
                    <a href="{{ route('mosques.show', $mosque) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('mosques.edit', $mosque) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('mosques.destroy', $mosque) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">لا توجد مساجد</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $mosques->links() }}
</div>
@endsection
