@extends('layouts.storefront')

@section('title', $order->order_number.' | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="section-heading">
            <div>
                <div class="mini-label">Order Details</div>
                <h1 class="display-5 fw-black">{{ $order->order_number }}</h1>
            </div>
            <a class="btn-ghost" href="{{ route('orders.invoice', $order) }}"><i class="bi bi-download"></i> Invoice</a>
        </div>
        <div class="checkout-grid">
            <div class="panel">
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex gap-3 align-items-center">
                                        <img class="rounded-2" style="width:72px;height:72px;object-fit:cover;" src="{{ $item->product?->primary_image_url }}" alt="{{ $item->product_name }}">
                                        <div>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if (!empty($item->options))
                                                <div class="small text-muted" style="font-size: 0.8rem;">
                                                    @foreach ($item->options as $key => $value)
                                                        {{ $key }}: {{ $value }}@if (! $loop->last) | @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>৳{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <aside class="panel panel-pad">
                <h2 class="fw-black">Delivery Timeline</h2>
                @foreach ($order->shipment?->events ?? [] as $event)
                    <div class="border-start border-primary ps-3 pb-4">
                        <strong>{{ strtoupper($event->status) }}</strong>
                        <p class="mb-1">{{ $event->notes }}</p>
                        <small class="muted">{{ $event->location }} • {{ $event->occurred_at->format('M d, h:i A') }}</small>
                    </div>
                @endforeach
                @if (in_array($order->status, ['pending', 'confirmed', 'processing'], true))
                    <form method="post" action="{{ route('orders.cancel', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn-ghost w-100 text-danger" type="submit">Cancel Order</button>
                    </form>
                @endif
            </aside>
        </div>
    </section>
@endsection
