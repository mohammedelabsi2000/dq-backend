@extends('layouts.app')

@section('content')
<div class="container">

    <h2>تفاصيل الفرع</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>الرقم:</strong> {{ $branch->id }}</p>
            <p><strong>الاسم:</strong> {{ $branch->name }}</p>
        </div>
    </div>

    <a href="{{ route('branches.index') }}" class="btn btn-secondary mt-3">رجوع</a>

</div>
@endsection
