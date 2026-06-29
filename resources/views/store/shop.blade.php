@extends('layouts.storefront')

@section('title', 'Shop | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="row g-5">
            <aside class="col-lg-3">
                <form method="get" class="position-sticky" style="top:96px;">
                    <div class="mb-4">
                        <div class="table-label">Categories</div>
                        <select class="form-select" name="category_id">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @foreach ($category->children as $child)
                                    <option value="{{ $child->id }}" @selected((string) request('category_id') === (string) $child->id)>- {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Search</label>
                        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search all products...">
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label">Min Price</label>
                            <input class="form-control" name="price_min" type="number" min="0" value="{{ request('price_min') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max Price</label>
                            <input class="form-control" name="price_max" type="number" min="0" value="{{ request('price_max') }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Minimum Rating</label>
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
                    <a href="{{ route('shop.index') }}" class="btn-ghost w-100 mt-2">Reset Filters</a>
                </form>
            </aside>
            <div class="col-lg-9">
                <div class="section-heading">
                    <div>
                        <h1 class="fw-black display-4 mb-1">All Products</h1>
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
                            <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                            <option value="rating" @selected(request('sort') === 'rating')>Top Rated</option>
                        </select>
                    </form>
                </div>
                <div class="product-grid">
                    @forelse ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @empty
                        <div class="panel panel-pad">
                            <h2 class="fw-black">No products found</h2>
                            <p class="muted mb-0">Try a wider price range or remove one of the filters.</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $products->links() }}</div>
            </div>
        </div>
    </section>
@endsection
