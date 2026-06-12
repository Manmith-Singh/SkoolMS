@extends('layouts.auth')
@section('content')
<h4 class="mb-3 text-center">Sign in</h4>
<p class="text-muted small text-center mb-4">Use the credentials from your school registration or super-admin account.</p>

<form method="POST" action="{{ route('master.login.attempt') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    <button class="btn btn-primary w-100" type="submit">
        <i class="fas fa-sign-in-alt me-1"></i> Sign in
    </button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('master.register') }}" class="text-decoration-none">Register a new school</a>
</div>
@endsection
