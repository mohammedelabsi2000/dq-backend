@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Academic Qualification</h2>

    <form action="{{ route('academic-qualifications.update', $academicQualification->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="person_type">Person Type</label>
            <input type="text" name="person_type" value="{{ $academicQualification->person_type }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="person_id">Person ID</label>
            <input type="number" name="person_id" value="{{ $academicQualification->person_id }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="academic_degree_id">Degree</label>
            <select name="academic_degree_id" class="form-select" required>
                <option value="">Select Degree</option>
                @foreach($degrees as $d)
                    <option value="{{ $d->id }}" {{ $academicQualification->academic_degree_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="major_id">Major</label>
            <select name="major_id" class="form-select" required>
                <option value="">Select Major</option>
                @foreach($majors as $m)
                    <option value="{{ $m->id }}" {{ $academicQualification->major_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="detail">Detail</label>
            <input type="text" name="detail" value="{{ $academicQualification->detail }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="date_graduate">Graduation Date</label>
            <input type="date" name="date_graduate" value="{{ $academicQualification->date_graduate }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="certificate_link">Certificate Link</label>
            <input type="text" name="certificate_link" value="{{ $academicQualification->certificate_link }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="educational_institution">Educational Institution</label>
            <input type="text" name="educational_institution" value="{{ $academicQualification->educational_institution }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="notes">Notes</label>
            <textarea name="notes" class="form-control">{{ $academicQualification->notes }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('academic-qualifications.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
