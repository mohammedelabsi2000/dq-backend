@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة المناطق</h2>

    <a href="{{ route('regions.create') }}" class="btn btn-primary mb-3">إضافة منطقة جديدة</a>

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
                <th>ملاحظات</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
        @forelse($regions as $region)
            <tr>
                <td>{{ $region->id }}</td>
                <td>{{ $region->name }}</td>
                <td>{{ $region->branch->name ?? '-' }}</td>
                <td>{{ $region->notes ?? '-' }}</td>
                <td>
                    <a href="{{ route('regions.show', $region) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('regions.edit', $region) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('regions.destroy', $region) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">لا توجد مناطق</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $regions->links() }}
</div>
@endsection
