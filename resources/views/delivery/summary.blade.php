@extends('layouts.portal')

@section('title', 'Delivery Summary | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Daily & Weekly Summary</h1><p class="fs-5 muted mt-2">Delivered, failed, and in-transit logistics totals.</p></div></div>

    <section class="metric-grid">
        <article class="metric-card"><div class="table-label">Delivered Today</div><strong>{{ $daily['delivered'] }}</strong></article>
        <article class="metric-card danger"><div class="table-label">Failed Today</div><strong>{{ $daily['failed'] }}</strong></article>
        <article class="metric-card"><div class="table-label">Delivered This Week</div><strong>{{ $weekly['delivered'] }}</strong></article>
        <article class="metric-card"><div class="table-label">In Transit</div><strong>{{ $weekly['in_transit'] }}</strong></article>
    </section>

    <section class="dashboard-grid">
        <article class="panel panel-pad">
            <h2 class="fw-black">Weekly Delivery Performance</h2>
            <div class="bar-chart">
                @foreach ([36, 52, 66, 44, 80, 72, 88] as $value)
                    <span class="{{ $value === 88 ? 'active' : '' }}" style="height: {{ $value }}%;"></span>
                @endforeach
            </div>
            <div class="d-flex justify-content-between muted mt-3"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
        </article>
        <article class="panel">
            <div class="panel-pad"><h2 class="fw-black mb-0">Recent Shipments</h2></div>
            <table class="data-table">
                <tbody>
                    @foreach ($shipments as $shipment)
                        <tr><td><strong>{{ $shipment->tracking_number }}</strong></td><td>{{ $shipment->order->order_number }}</td><td><span class="status-pill blue">{{ str_replace('_', ' ', $shipment->status) }}</span></td></tr>
                    @endforeach
                </tbody>
            </table>
        </article>
    </section>
@endsection
