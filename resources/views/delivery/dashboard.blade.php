@extends('layouts.portal')

@section('title', 'Logistics Dashboard | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Logistics Dashboard</h1>
            <p class="fs-5 muted mt-2">Real-time shipping and fulfillment monitoring.</p>
        </div>
        <div class="d-flex gap-3">
            <input class="form-control" placeholder="Search orders...">
            <a class="btn-ash" href="{{ route('delivery.dispatch') }}"><i class="bi bi-plus"></i> New Dispatch</a>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card"><i class="bi bi-calendar2-check fs-2 text-primary"></i><div class="mt-4 fs-5 muted">Pending Dispatch</div><strong>{{ $pendingDispatch }}</strong><span class="text-primary fw-bold">+12% vs last week</span></article>
        <article class="metric-card"><i class="bi bi-truck fs-2 text-primary"></i><div class="mt-4 fs-5 muted">Active Deliveries</div><strong>{{ $activeDeliveries }}</strong><span class="text-primary fw-bold">On Track</span></article>
        <article class="metric-card"><i class="bi bi-check-circle fs-2 text-success"></i><div class="mt-4 fs-5 muted">Delivered Today</div><strong>{{ $deliveredToday }}</strong><span class="text-success fw-bold">Completed</span></article>
        <article class="metric-card danger"><i class="bi bi-exclamation-triangle fs-2 text-danger"></i><div class="mt-4 fs-5 text-danger">Failed Deliveries</div><strong>{{ $failedDeliveries }}</strong><span class="text-danger fw-bold">Action Required</span></article>
    </section>

    <section class="dashboard-grid">
        <article class="panel panel-pad">
            <div class="section-heading">
                <h2>Delivery Performance</h2>
                <div class="auth-tabs" style="width:220px;margin:0;"><button class="active" type="button">Daily</button><button type="button">Weekly</button></div>
            </div>
            <div class="bar-chart">
                @foreach ([44, 70, 55, 92, 64, 81, 100] as $value)
                    <span class="{{ $value === 92 ? 'active' : '' }}" style="height: {{ $value }}%;"></span>
                @endforeach
            </div>
            <div class="d-flex justify-content-between muted mt-3"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
        </article>

        <article class="panel panel-pad bg-black text-white">
            <h3 class="text-white-50">Urgent Dispatch</h3>
            <h2 class="fw-black display-6">{{ $urgentShipment?->order?->order_number ?? '#ASH-8892-NY' }}</h2>
            <dl class="row fs-5 mt-4">
                <dt class="col-5 text-white-50">Destination</dt><dd class="col-7">{{ $urgentShipment?->order?->shipping_address ?? 'Dhaka City' }}</dd>
                <dt class="col-5 text-white-50">Carrier</dt><dd class="col-7">{{ $urgentShipment?->carrier ?? 'Ashvalian Fleet' }}</dd>
                <dt class="col-5 text-white-50">ETA</dt><dd class="col-7">{{ $urgentShipment?->estimated_delivery_at?->format('M d, h:i A') ?? 'Today, 4:00 PM' }}</dd>
            </dl>
            <a class="btn-ghost bg-white w-100 mt-5" href="{{ route('delivery.active') }}">Contact Courier</a>
        </article>
    </section>

    <section class="panel">
        <div class="panel-pad d-flex justify-content-between align-items-center">
            <h2 class="fw-black mb-0">Incoming Orders</h2>
            <div class="d-flex gap-3">
                <a class="btn-ghost py-2" href="{{ route('delivery.incoming') }}"><i class="bi bi-funnel"></i> Filters</a>
                <button class="btn-ghost py-2" type="button"><i class="bi bi-download"></i> Export CSV</button>
            </div>
        </div>
        <table class="data-table">
            <thead><tr><th>Order ID</th><th>Customer</th><th>Destination</th><th>Items</th><th>Dispatch Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($incomingOrders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->shipping_address }}</td>
                        <td>
                            @foreach ($order->items as $item)
                                <div class="mb-3">
                                    <strong>{{ $item->product_name }}</strong>
                                    @include('delivery.partials.item-status-update', ['item' => $item])
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $order->placed_at?->format('M d, Y') }}</td>
                        <td><span class="status-pill blue">{{ $order->shipment?->status ?? $order->status }}</span></td>
                        <td>
                            @if ($order->shipment)
                                <form method="post" action="{{ route('delivery.shipments.update', $order->shipment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button class="btn btn-sm" type="submit" aria-label="Process"><i class="bi bi-three-dots-vertical"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
