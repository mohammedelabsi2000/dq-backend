@extends('layouts.app')

@section('content')
<h2>المسارات</h2>

<a href="{{ route('tracks.create') }}">إضافة مسار جديد</a>

@foreach($tracks as $track)
    <div style="margin-bottom:15px; padding:10px; border:1px solid #ccc;">
        <h4>{{ $track->name }}</h4>
        <p>{{ $track->description }}</p>

        <a href="{{ route('tracks.show',$track->id) }}">عرض</a> |
        <a href="{{ route('tracks.edit',$track->id) }}">تعديل</a> |

        <form action="{{ route('tracks.destroy',$track->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit">حذف</button>
        </form>
    </div>
@endforeach

@endsection
