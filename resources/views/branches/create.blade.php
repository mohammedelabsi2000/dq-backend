@extends('layouts.app')

@section('content')
<div class="container">

    <h2>إضافة فرع</h2>

    <form action="{{ route('branches.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>اسم الفرع</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button class="btn btn-success">حفظ</button>
    </form>

</div>
@endsection
