@extends('layouts.portal')

@section('title', 'Marketplace Reports | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title"><h1>Monthly Marketplace Report</h1><p class="fs-5 muted mt-2">{{ $start->format('F Y') }} comprehensive summary.</p></div>
        <form method="get" class="d-flex gap-2">
            <input class="form-control" type="month" name="month" value="{{ $month }}">
            <button class="btn-ghost" type="submit">View</button>
            <a class="btn-ash" href="{{ route('admin.reports.index', ['month' => $month, 'print' => 1]) }}" onclick="setTimeout(() => window.print(), 300)"><i class="bi bi-printer"></i> Printable HTML</a>
        </form>
    </div>

    <section class="metric-grid">
        <article class="metric-card"><div class="table-label">Registered Users</div><strong>{{ number_format($totalUsers) }}</strong></article>
        <article class="metric-card"><div class="table-label">Customers</div><strong>{{ number_format($customers) }}</strong></article>
        <article class="metric-card"><div class="table-label">Orders</div><strong>{{ number_format($totalOrders) }}</strong></article>
        <article class="metric-card"><div class="table-label">Revenue</div><strong>৳{{ number_format($totalRevenue, 0) }}</strong></article>
    </section>

    <section class="dashboard-grid">
        <article class="panel">
            <div class="panel-pad"><h2 class="fw-black mb-0">Best Sellers</h2></div>
            <table class="data-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                <tbody>
                    @foreach ($bestSellers as $item)
                        <tr><td><strong>{{ $item->product_name }}</strong></td><td>{{ $item->quantity }}</td><td>৳{{ number_format($item->revenue, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </article>
        <article class="panel">
            <div class="panel-pad"><h2 class="fw-black mb-0">Low Stock</h2></div>
            <table class="data-table">
                <thead><tr><th>Product</th><th>Stock</th><th>Threshold</th></tr></thead>
                <tbody>
                    @foreach ($lowStockProducts as $product)
                        <tr><td>{{ $product->name }}</td><td class="text-danger fw-bold">{{ $product->stock_quantity }}</td><td>{{ $product->low_stock_threshold }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </article>
    </section>

    <section class="panel mt-4">
        <div class="panel-pad"><h2 class="fw-black mb-0">Orders</h2></div>
        <table class="data-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr><td>{{ $order->order_number }}</td><td>{{ $order->customer_name }}</td><td>{{ $order->placed_at?->format('M d, Y') }}</td><td>৳{{ number_format($order->grand_total, 2) }}</td><td><span class="status-pill blue">{{ $order->status }}</span></td></tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
