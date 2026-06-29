@extends('layouts.storefront')

@section('title', 'Choose New Password | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="mx-auto panel panel-pad" style="max-width: 560px;">
            <h1 class="display-6 fw-black mb-3">Choose New Password</h1>
            <p class="muted mb-4">OTP verified for <strong>{{ $pendingPasswordReset->email }}</strong>. Set a new password for your account.</p>

            <form method="post" action="{{ route('password.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input class="form-control" name="password" type="password" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input class="form-control" name="password_confirmation" type="password" required>
                </div>
                <button class="btn-ash w-100" type="submit">Update Password</button>
            </form>
        </div>
    </section>
@endsection
