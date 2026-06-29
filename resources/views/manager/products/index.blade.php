@extends('layouts.portal')

@section('title', 'Product Management | Ashvalian')

@section('content')
    <div class="portal-header">
        <div class="portal-title">
            <h1>Product Management</h1>
            <p class="fs-5 muted mt-2">Update, track and categorize your luxury inventory.</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn-ghost" type="button"><i class="bi bi-download"></i> Export CSV</button>
            <a class="btn-ash" href="{{ route('manager.products.create') }}"><i class="bi bi-plus"></i> New Product</a>
        </div>
    </div>

    <section class="metric-grid">
        <article class="metric-card"><div class="table-label">Total Products</div><strong>{{ number_format($totalProducts) }}</strong><span class="text-primary fw-bold">+12% from last month</span></article>
        <article class="metric-card"><div class="table-label">Low Stock Items</div><strong class="text-danger">{{ $lowStockCount }}</strong><span class="muted">Needs immediate restock</span></article>
        <article class="metric-card"><div class="table-label">Pending Returns</div><strong>{{ $pendingReturns }}</strong><span class="muted">Average 24h response</span></article>
        <article class="metric-card"><div class="table-label">Avg. Rating</div><strong>{{ number_format($averageRating, 1) }}/5</strong><span class="text-primary fw-bold">Based on reviews</span></article>
    </section>

    <section class="panel mb-5">
        <div class="panel-pad d-flex justify-content-between align-items-center">
            <h2 class="fw-black mb-0">Live Inventory</h2>
            <input class="form-control w-auto" placeholder="Search products...">
        </div>
        <table class="data-table">
            <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Stock</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex gap-3 align-items-center">
                                <img class="rounded-2" style="width:58px;height:58px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                                <div><strong>{{ $product->name }}</strong><div class="muted small">{{ implode(' / ', array_slice($product->colors ?? [], 0, 2)) }}</div></div>
                            </div>
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td><span class="status-pill">{{ $product->category?->name }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;min-width:80px;"><div class="progress-bar {{ $product->is_low_stock ? 'bg-danger' : 'bg-primary' }}" style="width: {{ min(100, $product->stock_quantity) }}%;"></div></div>
                                <strong class="{{ $product->is_low_stock ? 'text-danger' : '' }}">{{ $product->stock_quantity }}</strong>
                            </div>
                        </td>
                        <td><strong>৳{{ number_format($product->price, 2) }}</strong></td>
                        <td><span class="status-pill {{ $product->is_low_stock ? 'red' : 'blue' }}">{{ $product->is_low_stock ? 'Low Stock' : $product->status }}</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm" href="{{ route('manager.products.edit', $product) }}" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="{{ route('manager.products.availability', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm text-primary" type="submit" aria-label="Toggle availability"><i class="bi bi-eye-slash"></i></button>
                                </form>
                                <form method="post" action="{{ route('manager.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm text-danger" type="submit" aria-label="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $products->links() }}</div>
    </section>



    <section class="section">
        <h2 class="fw-black mb-4">Product Categories</h2>
        <div class="product-grid">
            @foreach ($categories as $category)
                <a class="category-tile d-flex align-items-center justify-content-center text-decoration-none" href="{{ route('category.show', $category->slug) }}" style="background: linear-gradient(135deg, #111115 0%, #1f1f2e 100%); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); min-height: 180px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                    <div class="text-center text-white p-3">
                        <div class="mb-2"><i class="bi bi-folder2 text-primary fs-3"></i></div>
                        <h3 class="h4 fw-bold mb-1 text-white">{{ $category->name }}</h3>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 uppercase-badge" style="font-size: 10px;">{{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
