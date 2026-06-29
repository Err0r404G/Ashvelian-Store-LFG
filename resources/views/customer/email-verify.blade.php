@extends('layouts.storefront')

@section('title', 'Verify Email Change | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="mx-auto panel panel-pad" style="max-width: 560px;">
            @include('partials.flash')
            <h1 class="display-6 fw-black mb-3">Verify New Email</h1>
            <p class="muted mb-4">Enter the 6-digit OTP sent to <strong>{{ $pendingEmailChange->new_email }}</strong>. Your login email will change only after this OTP matches.</p>

            @if ($demoOtp)
                <div class="alert alert-info">Demo OTP: <strong>{{ $demoOtp }}</strong></div>
            @endif

            <form method="post" action="{{ route('customer.profile.email.verify.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">OTP Code</label>
                    <input class="form-control text-center fs-3 fw-black" name="otp" inputmode="numeric" maxlength="6" placeholder="000000" required autofocus>
                </div>
                <button class="btn-ash w-100" type="submit">Verify Email</button>
            </form>

            <form method="post" action="{{ route('customer.profile.email.resend') }}" class="mt-3">
                @csrf
                <button class="btn-ghost w-100" type="submit">Resend OTP</button>
            </form>

            <a class="d-inline-flex align-items-center gap-2 mt-4 fw-bold text-decoration-none" href="{{ route('customer.profile.edit') }}">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
        </div>
    </section>
@endsection
