@extends('layouts.storefront')

@section('title', 'Forgot Password | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="mx-auto panel panel-pad" style="max-width: 560px;">
            <h1 class="display-6 fw-black mb-3">Reset Password</h1>
            <p class="muted mb-4">Enter your account email. If it exists, we will send a 6-digit OTP before you can choose a new password.</p>

            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </div>
                <button class="btn-ash w-100" type="submit">Send OTP</button>
            </form>

            <a class="d-inline-flex align-items-center gap-2 mt-4 fw-bold text-decoration-none" href="{{ route('login') }}">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>
    </section>
@endsection
