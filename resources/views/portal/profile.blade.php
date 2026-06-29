@extends('layouts.portal')

@section('title', 'Profile Settings | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Profile Settings</h1>
            <p class="fs-5 muted mt-2">Manage your portal identity, contact details, and password.</p>
        </div>
    </div>

    <section class="dashboard-grid">
        <form method="post" action="{{ route('portal.profile.update') }}" class="panel panel-pad">
            @csrf
            @method('PATCH')
            <h2 class="fw-black mb-4">Personal Details</h2>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" name="email" type="email" value="{{ old('email', $user->email) }}" required @disabled(!$user->isAdmin())>
                @if (!$user->isAdmin())
                    <div class="form-text text-muted">Email updates are restricted to administrators only.</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <button class="btn-ash" type="submit">Save Profile</button>
        </form>

        <form method="post" action="{{ route('portal.password.update') }}" class="panel panel-pad">
            @csrf
            @method('PATCH')
            <h2 class="fw-black mb-4">Change Password</h2>
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input class="form-control" name="current_password" type="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input class="form-control" name="password" type="password" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input class="form-control" name="password_confirmation" type="password" required>
            </div>
            <button class="btn-ghost" type="submit">Update Password</button>
        </form>
    </section>
@endsection
