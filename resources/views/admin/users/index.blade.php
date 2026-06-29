@extends('layouts.portal')

@section('title', 'Account Management | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>User Directory</h1>
            <p class="fs-5 muted mt-2">Search, view, and restrict or unrestrict customer access.</p>
        </div>
        <a class="btn-ash" href="{{ route('admin.staff.create') }}"><i class="bi bi-person-plus"></i> Create Staff</a>
    </div>

    <section class="metric-grid">
        @foreach (['admin' => 'Admins', 'manager' => 'Managers', 'delivery_manager' => 'Delivery Managers', 'customer' => 'Customers'] as $role => $label)
            <article class="metric-card">
                <div class="table-label">{{ $label }}</div>
                <strong>{{ number_format($roleCounts[$role] ?? 0) }}</strong>
            </article>
        @endforeach
    </section>

    <section class="panel">
        <div class="panel-pad">
            <form method="get" class="row g-3">
                <div class="col-md-5"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search by name, phone, or email..."></div>
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="">All roles</option>
                        @foreach (['admin' => 'Admin', 'manager' => 'Manager', 'delivery_manager' => 'Delivery Manager', 'customer' => 'Customer'] as $role => $label)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn-ghost w-100" type="submit">Filter</button></div>
            </form>
        </div>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->phone ?: 'Not provided' }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="status-pill blue">{{ str_replace('_', ' ', $user->role) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="status-pill {{ $user->is_restricted ? 'red' : 'green' }}">{{ $user->is_restricted ? 'Restricted' : 'Active' }}</span>
                                @if ($user->isCustomer())
                                    <form method="post" action="{{ route('admin.customers.restriction', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-ghost py-1" type="submit">{{ $user->is_restricted ? 'Unrestrict' : 'Restrict' }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $users->links() }}</div>
    </section>
@endsection
