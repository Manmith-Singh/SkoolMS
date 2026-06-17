@extends('layouts.app')
@section('title', 'Edit Exam Type')

@section('content')
<h4 class="mb-3">Edit Exam Type</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('exam-types.update', $examType) }}">
        @csrf @method('PUT')
        @include('exam_types._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('exam-types.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
