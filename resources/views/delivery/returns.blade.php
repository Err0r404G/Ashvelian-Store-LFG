@extends('layouts.portal')

@section('title', 'Failed & Returned Shipments | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <div class="mini-label">Delivery Management</div>
            <h1>Failed & Returned Shipments</h1>
            <p class="fs-5 muted mt-2">Resolve logistics exceptions, initiate re-deliveries, and manage inventory returns.</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn-ghost" type="button"><i class="bi bi-download"></i> Export CSV</button>
            <button class="btn-ash" type="button"><i class="bi bi-lightning-charge"></i> Bulk Update</button>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card danger"><div class="table-label text-danger">High-Visibility Return</div><strong>8</strong><p class="text-danger">Packages reaching warehouse storage limit.</p></article>
        <article class="metric-card" style="background:#e9f1ff;border-color:#b6cdf5;"><div class="table-label text-primary">Validation Pending</div><strong>24</strong><p>Orders stalled due to invalid zip codes or missing apartment numbers.</p></article>
        <article class="metric-card"><div class="table-label">Recipient Unavailable</div><strong>15</strong><p class="muted">Final attempt reached for high-value signature items.</p></article>
        <article class="metric-card"><div class="table-label">Success Rate</div><strong>94.2%</strong><p class="text-primary fw-bold">Retries recovering revenue.</p></article>
    </section>

    <section class="panel mb-5">
        <div class="panel-pad d-flex justify-content-between align-items-center">
            <h2 class="fw-black mb-0">Shipment Resolution Queue</h2>
            <input class="form-control w-auto" placeholder="Search Tracking ID or Customer">
        </div>
        <table class="data-table">
            <thead><tr><th>Order / Tracking</th><th>Reason for Failure</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($returnRequests as $request)
                    <tr>
                        <td><strong>{{ $request->order->order_number }}</strong><div class="muted small">{{ $request->order->shipment?->tracking_number }}</div></td>
                        <td>{{ $request->reason }}</td>
                        <td><span class="status-pill red">{{ str_replace('_', ' ', $request->status) }}</span></td>
                        <td><a class="text-primary fw-bold me-4" href="{{ route('delivery.active') }}">Retry Delivery</a><span>Return to WH</span></td>
                    </tr>
                @endforeach
                @foreach ($failedShipments as $shipment)
                    <tr>
                        <td><strong>{{ $shipment->order->order_number }}</strong><div class="muted small">{{ $shipment->tracking_number }}</div></td>
                        <td>{{ $shipment->tracking_notes ?: 'Recipient unavailable' }}</td>
                        <td><span class="status-pill red">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                        <td><a class="text-primary fw-bold me-4" href="{{ route('delivery.active') }}">Retry Delivery</a><span>Return to WH</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="dashboard-grid">
        <article class="panel panel-pad bg-black text-white">
            <h2 class="fw-black">Warehouse Recovery Optimization</h2>
            <p class="fs-5 text-white-50">Returned fitness equipment now routes to the nearest regional hub rather than the central origin.</p>
            <div class="d-flex gap-5 mt-5">
                <div><strong class="display-5">2.4h</strong><div class="table-label text-white">Avg. Processing Time</div></div>
                <div><strong class="display-5">৳12k</strong><div class="table-label text-white">Saved in Shipping</div></div>
            </div>
        </article>
        <article class="panel panel-pad text-center text-white" style="background:var(--ash-blue);">
            <i class="bi bi-rocket-takeoff display-4"></i>
            <h2 class="fw-black mt-3">Logistics Insights</h2>
            <p>Compare your routes to luxury industry benchmarks.</p>
            <a class="btn-ghost bg-white" href="{{ route('delivery.dashboard') }}">View Analytics</a>
        </article>
    </section>
@endsection
