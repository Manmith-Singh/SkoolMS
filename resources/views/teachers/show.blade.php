@extends('layouts.app')
@section('title', $teacher->full_name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">{{ $teacher->full_name }}</h4>
    <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card p-3">
    <table class="table mb-0">
        <tr><th>Employee ID</th><td>{{ $teacher->employee_id }}</td></tr>
        <tr><th>Email</th><td>{{ $teacher->email }}</td></tr>
        <tr><th>Phone</th><td>{{ $teacher->phone ?? '—' }}</td></tr>
        <tr><th>Subject</th><td>{{ $teacher->subject->name ?? '—' }}</td></tr>
        <tr><th>Qualification</th><td>{{ $teacher->qualification ?? '—' }}</td></tr>
        <tr><th>Hire date</th><td>{{ optional($teacher->hire_date)->format('d M Y') ?? '—' }}</td></tr>
        <tr><th>Salary</th><td>{{ number_format($teacher->salary ?? 0, 2) }}</td></tr>
        <tr><th>Address</th><td>{{ $teacher->address ?? '—' }}</td></tr>
    </table>
</div>
@endsection
