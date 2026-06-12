@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h4 class="mb-3">Reports</h4>
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('reports.results') }}" class="text-decoration-none">
            <div class="card p-4 text-center">
                <i class="fas fa-chart-bar fa-3x text-primary mb-2"></i>
                <h5>Exam results</h5>
                <p class="text-muted small mb-0">Filter by exam or class, see pass/fail, percentages.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reports.attendance') }}" class="text-decoration-none">
            <div class="card p-4 text-center">
                <i class="fas fa-calendar-check fa-3x text-success mb-2"></i>
                <h5>Attendance</h5>
                <p class="text-muted small mb-0">Monthly attendance by class with status counts.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reports.fees') }}" class="text-decoration-none">
            <div class="card p-4 text-center">
                <i class="fas fa-money-bill-wave fa-3x text-warning mb-2"></i>
                <h5>Fee collection</h5>
                <p class="text-muted small mb-0">Collected vs pending, broken down by category.</p>
            </div>
        </a>
    </div>
</div>
@endsection
