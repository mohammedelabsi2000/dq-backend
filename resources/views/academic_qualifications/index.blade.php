@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Academic Qualifications</h2>
    <a href="{{ route('academic-qualifications.create') }}" class="btn btn-primary mb-3">Add New</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Person</th>
                <th>Degree</th>
                <th>Major</th>
                <th>Graduation Date</th>
                <th>Institution</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($qualifications as $q)
            <tr>
                <td>{{ $q->id }}</td>
<td>{{ $q->person_name }}</td>
                <td>{{ $q->academicDegree->name ?? '-' }}</td>
                <td>{{ $q->major->name ?? '-' }}</td>
                <td>{{ $q->date_graduate ?? '-' }}</td>
                <td>{{ $q->educational_institution ?? '-' }}</td>
                <td>
                    <a href="{{ route('academic-qualifications.show', $q->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('academic-qualifications.edit', $q->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('academic-qualifications.destroy', $q->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $qualifications->links() }}
</div>
@endsection
