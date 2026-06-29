@extends('layouts.portal')

@section('title', 'Incoming Orders | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Incoming Order Items</h1>
            <p class="fs-5 muted mt-2">Filter and process individual items by fulfillment status.</p>
        </div>
        <a class="btn-ash" href="{{ route('delivery.dispatch') }}"><i class="bi bi-box-seam"></i> Ready Dispatch</a>
    </div>

    <section class="panel">
        <div class="panel-pad d-flex justify-content-between align-items-center">
            <h2 class="fw-black mb-0">Item Queue</h2>
            <form method="get" class="d-flex gap-2">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                    @endforeach
                </select>
                <button class="btn-ghost py-2" type="submit"><i class="bi bi-funnel"></i> Filter</button>
            </form>
        </div>
        <table class="data-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Item</th><th>Payment</th><th>Shipping Address</th><th>Update Status</th></tr></thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->order->order_number }}</strong>
                            <div class="small muted">{{ $item->order->placed_at?->format('M d, Y') }}</div>
                        </td>
                        <td>{{ $item->order->customer_name }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            <div class="small muted">SKU {{ $item->sku }} - Qty {{ $item->quantity }}</div>
                            @if (!empty($item->options))
                                <div class="small text-primary mt-1" style="font-size: 0.75rem; font-weight: 600;">
                                    @foreach ($item->options as $key => $value)
                                        {{ $key }}: {{ $value }}@if (! $loop->last) | @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>{{ strtoupper($item->order->payment_method) }}</td>
                        <td>{{ $item->order->shipping_address }}</td>
                        <td>@include('delivery.partials.item-status-update', ['item' => $item])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $items->links() }}</div>
    </section>
@endsection
