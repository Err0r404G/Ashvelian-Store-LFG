@extends('layouts.portal')

@section('title', 'Delivery History | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Delivery History</h1><p class="fs-5 muted mt-2">Completed, failed, and returned deliveries with timestamps.</p></div></div>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Tracking</th><th>Order</th><th>Customer</th><th>Status</th><th>Notes</th><th>Updated</th></tr></thead>
            <tbody>
                @foreach ($shipments as $shipment)
                    <tr>
                        <td><strong>{{ $shipment->tracking_number }}</strong></td>
                        <td>{{ $shipment->order->order_number }}</td>
                        <td>{{ $shipment->order->customer_name }}</td>
                        <td><span class="status-pill {{ $shipment->status === 'delivered' ? 'green' : 'red' }}">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                        <td>{{ $shipment->tracking_notes }}</td>
                        <td>{{ $shipment->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $shipments->links() }}</div>
    </section>
@endsection
