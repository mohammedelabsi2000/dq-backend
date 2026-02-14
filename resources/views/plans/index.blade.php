@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة الخطط</h2>

    <a href="{{ route('plans.create') }}" class="btn btn-primary mb-3">إضافة خطة جديدة</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الفئة المستهدفة</th>
                <th>عدد المستويات</th>
                <th>الوصف</th>
                <th>الملاحظات</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->type->name ?? '-' }}</td>
                <td>{{ $plan->targetGroup->name ?? '-' }}</td>
                <td>{{ $plan->level_numbers ?? '-' }}</td>
                <td>{{ $plan->description ?? '-' }}</td>
                <td>{{ $plan->notes ?? '-' }}</td>
                <td>
                    <a href="{{ route('plans.show', $plan) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('plans.destroy', $plan) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">لا توجد خطط</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $plans->links() }}
</div>
@endsection
