@extends('layouts.app')

@section('content')
<h2>الخطط المركبة</h2>

@foreach($plans as $plan)
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h4>{{ $plan->name }}</h4>
        الوزن: {{ $plan->weight }} | المدة: {{ $plan->duration_in_days }} يوم

        <br><br>

        <a href="{{ route('plans.setup.show', $plan->id) }}">عرض</a> |
        <a href="{{ route('plans.setup.edit', $plan->id) }}">تعديل</a> |

        <form action="{{ route('plans.setup.delete', $plan->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit">حذف</button>
        </form>
    </div>
@endforeach
@endsection
