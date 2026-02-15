@extends('layouts.app')

@section('content')
<h2>تركيب الخطة: {{ $plan->name }}</h2>

<form method="POST" action="{{ route('plans.setup.save', $plan->id) }}">
@csrf

@foreach($tracks as $track)
    @php
        $planTrack = $planTracks->firstWhere('track_id', $track->id);
    @endphp

    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <label>
            <input type="checkbox" name="tracks[{{ $track->id }}][is_required]"
                {{ $planTrack?->is_required ? 'checked' : '' }}>
            {{ $track->name }} (مسار)
        </label>

        <input type="number" name="tracks[{{ $track->id }}][weight]"
               value="{{ $planTrack->weight ?? 1 }}"
               placeholder="وزن المسار">

        <div style="margin-left:20px;">
            @foreach($track->courses as $course)
                @php
                    $planTrackCourse = $planTrack?->courses->firstWhere('course_id', $course->id);
                @endphp

                <label>
                    <input type="checkbox"
                           name="tracks[{{ $track->id }}][courses][{{ $course->id }}][is_required]"
                           {{ $planTrackCourse?->is_required ? 'checked' : '' }}>
                    {{ $course->name }} ({{ $course->hours }} ساعات)
                </label>

                <input type="number"
                       name="tracks[{{ $track->id }}][courses][{{ $course->id }}][order]"
                       value="{{ $planTrackCourse?->order ?? 1 }}"
                       placeholder="ترتيب">
                <br>
            @endforeach
        </div>
    </div>
@endforeach

<button type="submit">حفظ الخطة</button>
</form>

@endsection
