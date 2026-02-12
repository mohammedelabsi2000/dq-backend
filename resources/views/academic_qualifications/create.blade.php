@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Academic Qualification</h2>

    <form action="{{ route('academic-qualifications.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="person_type">Person Type</label>
            <input type="text" name="person_type" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="person_id">Person ID</label>
            <input type="number" name="person_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="academic_degree_id">Degree</label>
            <select name="academic_degree_id" class="form-select" required>
                <option value="">Select Degree</option>
                @foreach($degrees as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="major_id">Major</label>
            <select name="major_id" class="form-select" required>
                <option value="">Select Major</option>
                @foreach($majors as $m)
                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="detail">Detail</label>
            <input type="text" name="detail" class="form-control">
        </div>

        <div class="mb-3">
            <label for="date_graduate">Graduation Date</label>
            <input type="date" name="date_graduate" class="form-control">
        </div>

        <div class="mb-3">
            <label for="certificate_link">Certificate Link</label>
            <input type="text" name="certificate_link" class="form-control">
        </div>

        <div class="mb-3">
            <label for="educational_institution">Educational Institution</label>
            <input type="text" name="educational_institution" class="form-control">
        </div>

        <div class="mb-3">
            <label for="notes">Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('academic-qualifications.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
