@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">المراكز</h2>

    <a href="{{ route('centers.create') }}" class="btn btn-primary mb-3">إضافة مركز</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>المسجد</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
        @forelse($centers as $center)
            <tr>
                <td>{{ $center->id }}</td>
                <td>{{ $center->name }}</td>
                <td>{{ $center->mosque->name ?? '-' }}</td>
                <td>
                    <a href="{{ route('centers.show', $center) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('centers.edit', $center) }}" class="btn btn-warning btn-sm">تعديل</a>

                    <form action="{{ route('centers.destroy', $center) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                                onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">لا توجد مراكز</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $centers->links() }}
</div>
@endsection
