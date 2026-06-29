@extends('layouts.storefront')

@section('title', $category->name.' | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="row g-5">
            <aside class="col-lg-3">
                <form method="get" class="position-sticky" style="top:96px;">
                    <div class="mb-4">
                        <div class="table-label">Categories</div>
                        @forelse ($categories as $child)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category_id" value="{{ $child->id }}" @checked((string) request('category_id') === (string) $child->id)>
                                <label class="form-check-label">{{ $child->name }}</label>
                            </div>
                        @empty
                            <p class="muted">Showing {{ $category->name }} essentials.</p>
                        @endforelse
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Search</label>
                        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search elite gear...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Price Range</label>
                        <input type="range" class="form-range" min="25" max="300" name="price_max" value="{{ request('price_max', 250) }}">
                        <div class="d-flex justify-content-between"><span>৳0</span><strong>৳{{ request('price_max', 250) }}+</strong></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Rating</label>
                        <select class="form-select" name="rating">
                            <option value="">Any rating</option>
                            <option value="4" @selected(request('rating') === '4')>4 stars and up</option>
                            <option value="4.5" @selected(request('rating') === '4.5')>4.5 stars and up</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" name="in_stock" value="1" @checked(request()->boolean('in_stock'))>
                        <label class="form-check-label">In Stock Only</label>
                    </div>
                    <button class="btn-ghost w-100" type="submit">Apply Filters</button>
                    <a href="{{ route('category.show', $category->slug) }}" class="btn-ghost w-100 mt-2">Reset Filters</a>
                </form>
            </aside>
            <div class="col-lg-9">
                <div class="section-heading">
                    <div>
                        <h1 class="fw-black display-4 mb-1">{{ $category->name === 'Fitness' ? 'Performance Apparel' : $category->name }}</h1>
                        <p class="muted fs-5">Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</p>
                    </div>
                    <form method="get">
                        @foreach (request()->except('sort') as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select class="form-select bg-white" name="sort" onchange="this.form.submit()">
                            <option value="featured" @selected(request('sort') === 'featured')>Sort by: Featured</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                            <option value="rating" @selected(request('sort') === 'rating')>Top Rated</option>
                        </select>
                    </form>
                </div>
                <div class="product-grid">
                    @foreach ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-5">{{ $products->links() }}</div>
            </div>
        </div>
    </section>
@endsection
