@extends('layouts.portal')

@section('title', 'Active Deliveries | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Active Deliveries</h1>
            <p class="fs-5 muted mt-2">Real-time logistics management and transit tracking.</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn-ghost" type="button"><i class="bi bi-download"></i> Export Report</button>
            <a class="btn-ash" href="{{ route('delivery.dispatch') }}"><i class="bi bi-plus"></i> New Shipment</a>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card"><div class="table-label">Total In Transit</div><strong>{{ number_format($inTransit) }}</strong><span class="text-primary fw-bold">+12%</span></article>
        <article class="metric-card"><div class="table-label">Out For Delivery</div><strong>{{ number_format($outForDelivery) }}</strong><span class="text-primary fw-bold">Live</span></article>
        <article class="metric-card"><div class="table-label">Avg. Transit Time</div><strong>1.4d</strong><span class="text-danger fw-bold">+0.2d</span></article>
        <article class="metric-card"><div class="table-label">On-Time Rate</div><strong>{{ $onTimeRate }}%</strong><span class="text-primary fw-bold">Stable</span></article>
    </section>

    <section class="delivery-grid">
        <div class="panel">
            <div class="panel-pad d-flex justify-content-between">
                <input class="form-control w-50" placeholder="Filter by Order ID or Name...">
                <strong><i class="bi bi-filter"></i> Sort by: Latest</strong>
            </div>
            <table class="data-table">
                <thead><tr><th>Order ID</th><th>Customer Name</th><th>Destination</th><th>Carrier</th><th>Status</th><th>Update</th></tr></thead>
                <tbody>
                    @foreach ($shipments as $shipment)
                        <tr>
                            <td><a class="text-primary fw-bold" href="{{ route('delivery.shipments.show', $shipment) }}">{{ $shipment->order->order_number }}</a></td>
                            <td><strong>{{ $shipment->order->customer_name }}</strong><div class="muted small">Ashvalian Member</div></td>
                            <td>{{ $shipment->order->shipping_address }}</td>
                            <td><span class="status-pill">{{ \Illuminate\Support\Str::before($shipment->carrier, ' ') }}</span> {{ \Illuminate\Support\Str::after($shipment->carrier, ' ') }}</td>
                            <td><span class="status-pill {{ $shipment->status === 'failed' ? 'red' : 'blue' }}">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                            <td>
                                <form method="post" action="{{ route('delivery.shipments.update', $shipment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                        @foreach (['confirmed','processing','shipped','in_transit','out_for_delivery','delivered','failed','returned'] as $status)
                                            <option value="{{ $status }}" @selected($shipment->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="panel-pad">{{ $shipments->links() }}</div>
        </div>

        <aside>
            <article class="panel mb-4 overflow-hidden">
                <div class="panel-pad d-flex justify-content-between"><h3 class="fw-black">Transit Hotspots</h3><a class="text-primary fw-bold">Full Map View</a></div>
                <div class="map-panel p-4 d-flex align-items-end">
                    <div class="panel panel-pad text-dark">
                        <div class="table-label">Active Alerts</div>
                        <strong>Logistical delay at London Heathrow Hub</strong>
                    </div>
                </div>
                <div class="panel-pad">
                    <div class="table-label">Peak Region</div>
                    <h4 class="fw-black">North America - East Coast</h4>
                    <div class="progress" style="height:6px;"><div class="progress-bar" style="width:88%;"></div></div>
                    <div class="d-flex justify-content-between mt-2"><span class="table-label">Efficiency</span><strong>88%</strong></div>
                </div>
            </article>
            <article class="panel panel-pad bg-black text-white">
                <h3 class="fw-black">Fleet Performance</h3>
                <p class="text-white-50">All Ashvalian premium courier routes are currently operational.</p>
                <button class="btn-ghost bg-white" type="button">Fleet Dashboard</button>
            </article>
        </aside>
    </section>
@endsection
