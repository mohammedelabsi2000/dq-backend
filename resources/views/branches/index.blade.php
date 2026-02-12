@extends('layouts.app')

@section('content')
<div class="container">

    <h2>الفروع</h2>

    <a href="{{ route('branches.create') }}" class="btn btn-primary mb-3">إضافة فرع</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th width="200">التحكم</th>
            </tr>
        </thead>
        <tbody>
        @foreach($branches as $branch)
            <tr>
                <td>{{ $branch->id }}</td>
                <td>{{ $branch->name ?? '-' }}</td>
                <td>
                    <a href="{{ route('branches.show', $branch) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning btn-sm">تعديل</a>

                    <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $branches->links() }}

</div>
@endsection
