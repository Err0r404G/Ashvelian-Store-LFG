@extends('layouts.storefront')

@section('title', 'Verify Password OTP | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="mx-auto panel panel-pad" style="max-width: 560px;">
            <h1 class="display-6 fw-black mb-3">Verify Reset OTP</h1>
            <p class="muted mb-4">Enter the 6-digit OTP sent to <strong>{{ $pendingPasswordReset->email }}</strong>.</p>

            @if ($demoOtp)
                <div class="alert alert-info">Demo OTP: <strong>{{ $demoOtp }}</strong></div>
            @endif

            <form method="post" action="{{ route('password.verify.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">OTP Code</label>
                    <input class="form-control text-center fs-3 fw-black" name="otp" inputmode="numeric" maxlength="6" placeholder="000000" required autofocus>
                </div>
                <button class="btn-ash w-100" type="submit">Verify OTP</button>
            </form>

            <form method="post" action="{{ route('password.resend') }}" class="mt-3">
                @csrf
                <button class="btn-ghost w-100" type="submit">Resend OTP</button>
            </form>
        </div>
    </section>
@endsection
