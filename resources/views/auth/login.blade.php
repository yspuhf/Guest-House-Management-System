@extends('layouts.auth')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="login-card p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-buildings-fill text-success fs-1"></i>
                <h4 class="fw-bold mt-2 text-dark">University Guest House Portal</h4>
                <div class="text-muted small">Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni</div>
                <hr class="my-3">
                <span class="badge bg-light text-dark border px-3 py-1">Institutional SSO & Local Authentication</span>
            </div>

            @if(session('info'))
                <div class="alert alert-info py-2 small">{{ session('info') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">University Email ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@yspuniversity.ac.in') }}" placeholder="name@yspuniversity.ac.in" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" value="password123" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('bookings.track') }}" class="small text-decoration-none"><i class="bi bi-search"></i> Track Request</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Portal
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <small class="text-muted d-block mb-2">Default demo accounts (password: <code>password123</code>):</small>
                <div class="d-flex flex-wrap justify-content-center gap-1">
                    <span class="badge bg-secondary">admin@yspuniversity.ac.in</span>
                    <span class="badge bg-secondary">incharge@yspuniversity.ac.in</span>
                    <span class="badge bg-secondary">verifier@yspuniversity.ac.in</span>
                    <span class="badge bg-secondary">approver@yspuniversity.ac.in</span>
                    <span class="badge bg-secondary">employee@yspuniversity.ac.in</span>
                    <span class="badge bg-secondary">finance@yspuniversity.ac.in</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

