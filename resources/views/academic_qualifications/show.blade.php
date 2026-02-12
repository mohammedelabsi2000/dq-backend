@extends('layouts.app')

@section('content')
<div class="container">
    <h2>View Academic Qualification</h2>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $academicQualification->id }}</td>
        </tr>
        <tr>
            <th>Person</th>
            <td>{{ $academicQualification->person_type }} #{{ $academicQualification->person_id }}</td>
        </tr>
        <tr>
            <th>Degree</th>
            <td>{{ $academicQualification->academicDegree->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Major</th>
            <td>{{ $academicQualification->major->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Detail</th>
            <td>{{ $academicQualification->detail }}</td>
        </tr>
        <tr>
            <th>Graduation Date</th>
            <td>{{ $academicQualification->date_graduate }}</td>
        </tr>
        <tr>
            <th>Certificate Link</th>
            <td>{{ $academicQualification->certificate_link }}</td>
        </tr>
        <tr>
            <th>Institution</th>
            <td>{{ $academicQualification->educational_institution }}</td>
        </tr>
        <tr>
            <th>Notes</th>
            <td>{{ $academicQualification->notes }}</td>
        </tr>
    </table>

    <a href="{{ route('academic-qualifications.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
