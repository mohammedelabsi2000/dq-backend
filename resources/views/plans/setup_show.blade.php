@extends('layouts.app')

@section('content')
<h2>تفاصيل الخطة: {{ $plan->name }}</h2>

@foreach($plan->planTracks as $planTrack)
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h4>
            {{ $planTrack->track?->name ?? 'مسار محذوف' }}
            ({{ $planTrack->is_required ? 'إجباري' : 'اختياري' }})
        </h4>

        وزن المسار: {{ $planTrack->weight }}

        <ul>
        @foreach($planTrack->courses as $course)
            <li>
                {{ $course->name ?? 'دورة محذوفة' }} -
                {{ $course->hours ?? 0 }} ساعات -
                {{ $course->pivot->is_required ? 'إجباري' : 'اختياري' }} -
                ترتيب: {{ $course->pivot->order ?? 1 }}
            </li>
        @endforeach
        </ul>
    </div>
@endforeach

<div style="margin-top:20px;">
    <a href="{{ route('plans.assign.create', $plan->id) }}" class="btn btn-primary">إسناد الطلاب</a>
    <a href="{{ route('plans.students.show', $plan->id) }}" class="btn btn-secondary">عرض طلاب الخطة</a>
    <a href="{{ route('plans.setup.show.index') }}" class="btn btn-light">رجوع للقائمة</a>
</div>
@endsection
