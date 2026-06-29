@extends('layouts.portal')

@section('title', $shipment->tracking_number.' | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <div class="mini-label">Shipment Detail</div>
            <h1>{{ $shipment->tracking_number }}</h1>
            <p class="fs-5 muted mt-2">{{ $shipment->order->order_number }} - {{ $shipment->order->customer_name }}</p>
        </div>
        <a class="btn-ghost" href="{{ route('delivery.active') }}"><i class="bi bi-arrow-left"></i> Back to Active</a>
    </div>

    <div class="checkout-grid">
        <section class="panel">
            <div class="panel-pad border-bottom">
                <h2 class="fw-black mb-0">Order Items</h2>
            </div>
            <table class="data-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Item Status</th><th>Update</th></tr></thead>
                <tbody>
                    @foreach ($shipment->order->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <img class="rounded-2" style="width:64px;height:64px;object-fit:cover;" src="{{ $item->product?->primary_image_url }}" alt="{{ $item->product_name }}">
                                    <div>
                                        <strong>{{ $item->product_name }}</strong>
                                        <div class="small muted">{{ $item->sku }}</div>
                                        @if (!empty($item->options))
                                            <div class="small text-primary mt-1" style="font-size: 0.75rem; font-weight: 600;">
                                                @foreach ($item->options as $key => $value)
                                                    {{ $key }}: {{ $value }}@if (! $loop->last) | @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td><span class="status-pill {{ $item->status === 'delivered' ? 'green' : (in_array($item->status, ['failed', 'returned']) ? 'red' : 'blue') }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                            <td>@include('delivery.partials.item-status-update', ['item' => $item, 'showCurrent' => false])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <aside class="panel panel-pad">
            <h2 class="fw-black mb-4">Shipment Controls</h2>
            <p><strong>Address:</strong> {{ $shipment->order->shipping_address }}</p>
            <p><strong>Phone:</strong> {{ $shipment->order->customer_phone }}</p>
            <p><strong>Payment:</strong> {{ strtoupper($shipment->order->payment_method) }}</p>
            <form method="post" action="{{ route('delivery.shipments.update', $shipment) }}" class="d-grid gap-3">
                @csrf
                @method('PATCH')
                <select class="form-select" name="status">
                    @foreach (['pending_dispatch','confirmed','processing','shipped','in_transit','out_for_delivery','delivered','failed','returned'] as $status)
                        <option value="{{ $status }}" @selected($shipment->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <input class="form-control" name="location" placeholder="Current location">
                <textarea class="form-control" name="tracking_notes" rows="4" placeholder="Tracking notes">{{ $shipment->tracking_notes }}</textarea>
                <button class="btn-ash" type="submit">Update Delivery Status</button>
            </form>
        </aside>
    </div>

    <section class="panel mt-5">
        <div class="panel-pad border-bottom"><h2 class="fw-black mb-0">Delivery Timeline</h2></div>
        <table class="data-table">
            <thead><tr><th>Status</th><th>Location</th><th>Notes</th><th>Time</th></tr></thead>
            <tbody>
                @foreach ($shipment->events->sortByDesc('occurred_at') as $event)
                    <tr>
                        <td><span class="status-pill blue">{{ str_replace('_', ' ', $event->status) }}</span></td>
                        <td>{{ $event->location }}</td>
                        <td>{{ $event->notes }}</td>
                        <td>{{ $event->occurred_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
