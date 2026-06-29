@extends('layouts.portal')

@section('title', 'Create Staff Account | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Create Staff Account</h1>
            <p class="fs-5 muted mt-2">Create Admin, Manager, or Delivery Manager accounts only.</p>
        </div>
        <a class="btn-ghost" href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left"></i> User Directory</a>
    </div>

    <section class="panel panel-pad">
        <form method="post" action="{{ route('admin.staff.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input class="form-control" name="email" type="email" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select class="form-select" name="role" required>
                    @foreach ($staffRoles as $role => $label)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input class="form-control" name="password" type="password" required>
            </div>
            <div class="col-12">
                <button class="btn-ash" type="submit">Create Staff Account</button>
            </div>
        </form>
    </section>
@endsection
