@extends('layouts.storefront')

@section('title', 'Invoice '.$order->order_number)

@section('content')
    <section class="page-shell section invoice-page">
        <div class="panel panel-pad bg-white invoice-print-area">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <div class="brand">Ashvalian</div>
                    <p class="muted mb-0">Electronic Invoice</p>
                </div>
                <div class="text-end">
                    <h1 class="fw-black">{{ $order->order_number }}</h1>
                    <p class="muted">{{ $order->placed_at?->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Bill To</strong>
                    <p class="mb-0">{{ $order->customer_name }}</p>
                    <p class="mb-0">{{ $order->customer_email }}</p>
                    <p>{{ $order->customer_phone }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Ship To</strong>
                    <p>{{ $order->shipping_address }}</p>
                </div>
            </div>
            <table class="data-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>৳{{ number_format($item->unit_price, 2) }}</td><td>৳{{ number_format($item->line_total, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ms-auto mt-4" style="max-width:360px;">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong>৳{{ number_format($order->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><strong>৳{{ number_format($order->discount_total, 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Shipping</span><strong>৳{{ number_format($order->shipping_total, 2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Tax</span><strong>৳{{ number_format($order->tax_total, 2) }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between fs-4"><span>Total</span><strong>৳{{ number_format($order->grand_total, 2) }}</strong></div>
            </div>
            <button class="btn-ash mt-4 no-print" onclick="window.print()" type="button"><i class="bi bi-printer"></i> Print / Save PDF</button>
        </div>
    </section>
@endsection
