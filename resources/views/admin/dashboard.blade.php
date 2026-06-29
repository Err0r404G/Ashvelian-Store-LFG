@extends('layouts.portal')

@section('title', 'Admin Overview | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Overview</h1>
            <p class="fs-5 muted mt-2">Real-time performance tracking for Ashvalian Luxury Fitness.</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn-ghost" type="button"><i class="bi bi-calendar"></i> Last 30 Days</button>
            <button class="btn-ash" type="button"><i class="bi bi-download"></i> Export Report</button>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card">
            <div class="table-label">Total Users</div>
            <strong>{{ number_format($roleCounts->sum()) }}</strong>
            <span class="text-primary fw-bold">+12.5% vs last month</span>
        </article>
        <article class="metric-card">
            <div class="table-label">Monthly Revenue</div>
            <strong>৳{{ number_format($monthlyRevenue, 0) }}</strong>
            <span class="text-primary fw-bold">+8.2% vs last month</span>
        </article>
        <article class="metric-card">
            <div class="table-label">Today's Orders</div>
            <strong>{{ number_format($ordersToday) }}</strong>
            <span class="text-danger fw-bold">-3.1% vs yesterday</span>
        </article>
        <article class="metric-card">
            <div class="table-label">Active Customers</div>
            <strong>{{ number_format($activeCustomers) }}</strong>
            <span class="text-success fw-bold">Verified accounts</span>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel panel-pad">
            <div class="section-heading">
                <h2>Sales Velocity</h2>
                <div><span class="text-primary">●</span> Current <span class="muted ms-2">● Previous</span></div>
            </div>
            <div class="bar-chart">
                @foreach ([42, 62, 54, 82, 57, 70, 92] as $value)
                    <span class="{{ $value === 82 ? 'active' : '' }}" style="height: {{ $value }}%;"></span>
                @endforeach
            </div>
            <div class="d-flex justify-content-between muted mt-3"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
        </article>

        <article class="panel panel-pad">
            <h2 class="fw-black mb-4">Low Stock Alerts</h2>
            @foreach ($lowStockProducts as $product)
                <div class="d-flex gap-3 align-items-center p-3 mb-3 bg-light rounded-2 border-start border-danger border-4">
                    <img class="rounded-2" style="width:62px;height:62px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                    <div>
                        <strong>{{ $product->name }}</strong>
                        <div class="text-danger small">{{ $product->stock_quantity }} units remaining</div>
                    </div>
                </div>
            @endforeach
        </article>
    </section>

    <section class="panel mb-5">
        <div class="panel-pad d-flex justify-content-between align-items-center">
            <h2 class="fw-black mb-0">Best-selling Products</h2>
        </div>
        <table class="data-table">
            <thead><tr><th>Product</th><th>Sales</th><th>Revenue</th><th>Trend</th></tr></thead>
            <tbody>
                @foreach ($bestSellers as $product)
                    <tr>
                        <td><strong>{{ $product->product_name }}</strong></td>
                        <td>{{ number_format($product->sales) }}</td>
                        <td>৳{{ number_format($product->revenue, 2) }}</td>
                        <td class="text-primary"><i class="bi bi-graph-up-arrow"></i></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section id="team">
        <div class="section-heading">
            <h2>Team Management</h2>
            <a class="btn-ghost" href="{{ route('admin.staff.create') }}"><i class="bi bi-person-plus"></i> Create Staff</a>
        </div>
        <div class="row g-4">
            @foreach ($team as $member)
                <div class="col-md-4">
                    <article class="panel panel-pad">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary fw-black d-grid place-items-center" style="width:52px;height:52px;">{{ substr($member->name, 0, 1) }}</div>
                            <div>
                                <h4 class="fw-black mb-0">{{ $member->name }}</h4>
                                <div class="mini-label">{{ str_replace('_', ' ', $member->role) }}</div>
                            </div>
                        </div>
                        <hr>
                        <span class="muted"><i class="bi bi-shield-lock"></i> {{ $member->isAdmin() ? 'Full Access' : 'Limited Access' }}</span>
                    </article>
                </div>
            @endforeach
        </div>
    </section>

    <section id="reports" class="panel mt-5">
        <div class="panel-pad">
            <h2 class="fw-black">Activity Logs</h2>
        </div>
        <table class="data-table">
            <thead><tr><th>User</th><th>Action</th><th>Description</th><th>Time</th></tr></thead>
            <tbody>
                @foreach ($activityLogs as $log)
                    <tr>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="status-pill blue">{{ $log->action }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
