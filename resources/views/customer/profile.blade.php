@extends('layouts.storefront')

@section('title', 'Profile | Ashvalian')

@section('content')
    <section class="page-shell section">
        <h1 class="display-5 fw-black">Profile Settings</h1>
        <p class="fs-5 muted mb-5">Update your personal information, email security, and password.</p>

        <div class="checkout-grid">
            <form method="post" action="{{ route('customer.profile.update') }}" class="panel panel-pad">
                @csrf
                @method('PATCH')
                <h2 class="fw-black mb-4">Personal Information</h2>
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $user->name }}" required></div>
                <div class="mb-3">
                    <label class="form-label">Current Email</label>
                    <input class="form-control" value="{{ $user->email }}" disabled>
                    <div class="small muted mt-2">Email changes require your current password and an OTP sent to the new address.</div>
                </div>
                <button class="btn-ash" type="submit">Save Profile</button>
            </form>

            <form method="post" action="{{ route('customer.profile.email.request') }}" class="panel panel-pad">
                @csrf
                <h2 class="fw-black mb-4">Change Email</h2>
                @if ($emailChangeAvailableAt && $emailChangeAvailableAt->isFuture())
                    <div class="alert alert-warning">
                        You can change your email again on {{ $emailChangeAvailableAt->format('M j, Y') }}.
                    </div>
                @endif
                <div class="mb-3"><label class="form-label">New Email Address</label><input class="form-control" name="email" type="email" value="{{ old('email') }}" required @disabled($emailChangeAvailableAt && $emailChangeAvailableAt->isFuture())></div>
                <div class="mb-4"><label class="form-label">Current Password</label><input class="form-control" name="current_password" type="password" required @disabled($emailChangeAvailableAt && $emailChangeAvailableAt->isFuture())></div>
                <button class="btn-ash" type="submit" @disabled($emailChangeAvailableAt && $emailChangeAvailableAt->isFuture())>Send Email OTP</button>
            </form>

            <form method="post" action="{{ route('customer.password.update') }}" class="panel panel-pad">
                @csrf
                @method('PATCH')
                <h2 class="fw-black mb-4">Change Password</h2>
                <div class="mb-3"><label class="form-label">Current Password</label><input class="form-control" name="current_password" type="password" required></div>
                <div class="mb-3"><label class="form-label">New Password</label><input class="form-control" name="password" type="password" required></div>
                <div class="mb-4"><label class="form-label">Confirm Password</label><input class="form-control" name="password_confirmation" type="password" required></div>
                <button class="btn-ghost" type="submit">Update Password</button>
            </form>
        </div>
    </section>
@endsection
