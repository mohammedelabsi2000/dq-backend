@extends('layouts.app')

@section('content')
<div class="container">

    <h2>تعديل الفرع</h2>

    <form action="{{ route('branches.update', $branch) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم الفرع</label>
            <input type="text" name="name" value="{{ $branch->name }}" class="form-control" required>
        </div>

        <button class="btn btn-primary">تحديث</button>
    </form>

</div>
@endsection
