@extends('layouts.portal')

@section('title', 'Ready Dispatch | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Ready For Dispatch</h1>
            <p class="fs-5 muted mt-2">Orders waiting for logistics confirmation and shipment release.</p>
        </div>
        <a class="btn-ghost" href="{{ route('delivery.incoming') }}"><i class="bi bi-inboxes"></i> Incoming Items</a>
    </div>

    <section class="panel">
        <div class="panel-pad"><h2 class="fw-black mb-0">Dispatch Queue</h2></div>
        <table class="data-table">
            <thead><tr><th>Shipment</th><th>Order</th><th>Customer</th><th>Destination</th><th>Items</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($shipments as $shipment)
                    <tr>
                        <td>
                            <strong>{{ $shipment->tracking_number }}</strong>
                            <div class="small muted">{{ $shipment->carrier }}</div>
                        </td>
                        <td><a class="text-primary fw-bold" href="{{ route('delivery.shipments.show', $shipment) }}">{{ $shipment->order->order_number }}</a></td>
                        <td>{{ $shipment->order->customer_name }}</td>
                        <td>{{ $shipment->order->shipping_address }}</td>
                        <td>
                            @foreach ($shipment->order->items as $item)
                                <div><strong>{{ $item->product_name }}</strong> <span class="muted">x{{ $item->quantity }}</span></div>
                            @endforeach
                        </td>
                        <td>
                            <form method="post" action="{{ route('delivery.shipments.update', $shipment) }}" class="d-grid gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <input class="form-control form-control-sm" name="tracking_notes" placeholder="Dispatch note">
                                <button class="btn-ash py-2" type="submit">Confirm Dispatch</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center muted py-5">No orders are waiting for dispatch.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="panel-pad">{{ $shipments->links() }}</div>
    </section>
@endsection
