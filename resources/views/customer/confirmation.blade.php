@extends('layouts.storefront')

@section('title', 'Order Confirmed | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="panel panel-pad text-center mb-5">
            <div class="rounded-circle bg-primary text-white d-inline-grid place-items-center mb-4" style="width:72px;height:72px;">
                <i class="bi bi-check-lg fs-1"></i>
            </div>
            <div class="mini-label">Order Confirmation</div>
            <h1 class="display-5 fw-black">{{ $order->order_number }}</h1>
            <p class="fs-5 muted mb-2">Your order has been placed successfully.</p>
            <p class="mb-0">
                Estimated delivery:
                <strong>{{ $order->shipment?->estimated_delivery_at?->format('M d, Y') ?? now()->addDays($order->deliveryZone?->estimated_days ?? 3)->format('M d, Y') }}</strong>
            </p>
        </div>

        <div class="checkout-grid">
            <section class="panel">
                <div class="panel-pad border-bottom">
                    <h2 class="fw-black mb-0">Itemized Order</h2>
                </div>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Status</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex gap-3 align-items-center">
                                        <img class="rounded-2" style="width:72px;height:72px;object-fit:cover;" src="{{ $item->product?->primary_image_url }}" alt="{{ $item->product_name }}">
                                        <strong>{{ $item->product_name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td><span class="status-pill blue">{{ $item->status }}</span></td>
                                <td>৳{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <aside class="panel panel-pad">
                <h2 class="fw-black mb-4">Receipt Summary</h2>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>৳{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>৳{{ number_format($order->discount_total, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span>৳{{ number_format($order->shipping_total, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-4"><span>Tax</span><span>৳{{ number_format($order->tax_total, 2) }}</span></div>
                <hr>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h3 class="fw-black">Total</h3>
                    <strong class="display-6">৳{{ number_format($order->grand_total, 2) }}</strong>
                </div>
                <p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }} / {{ ucfirst($order->payment_status) }}</p>
                <p><strong>Ship to:</strong> {{ $order->shipping_address }}</p>
                <a class="btn-ash w-100 mb-3" href="{{ route('orders.show', $order) }}">Track Order</a>
                <a class="btn-ghost w-100" href="{{ route('orders.invoice', $order) }}"><i class="bi bi-download"></i> Download Invoice</a>
            </aside>
        </div>
    </section>
@endsection
