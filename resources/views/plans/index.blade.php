@extends('layouts.app')

@section('content')
<h2>الخطط</h2>

<a href="{{ route('plans.create') }}">إضافة خطة جديدة</a>

@foreach($plans as $plan)
    <div style="margin-bottom:15px; padding:10px; border:1px solid #ccc;">
        <h4>{{ $plan->name }}</h4>
        الوزن: {{ $plan->weight }} |
        المدة: {{ $plan->duration_in_days }} يوم |
        السماحية: {{ $plan->grace_period_days }} يوم |
        الحالة: {{ $plan->is_active ? 'مفعلة' : 'غير مفعلة' }}

        <br><br>

        <a href="{{ route('plans.show',$plan->id) }}">عرض</a> |
        <a href="{{ route('plans.edit',$plan->id) }}">تعديل</a> |
        <a href="{{ route('plans.setup', $plan->id) }}">تركيب الخطة</a> |

        <form action="{{ route('plans.destroy',$plan->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit">حذف</button>
        </form>
    </div>
@endforeach

@endsection
