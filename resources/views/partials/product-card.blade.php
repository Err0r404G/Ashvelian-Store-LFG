<article class="product-card">
    <a href="{{ route('products.show', $product->slug) }}" class="product-card-media">
        <img src="{{ $product->primary_image_url ?: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1000&q=80' }}" alt="{{ $product->name }}">
    </a>
    @if (auth()->check() && auth()->user()->isCustomer())
        <form method="post" action="{{ route('wishlist.toggle', $product) }}">
            @csrf
            <button class="wish-float" type="submit" aria-label="Save {{ $product->name }}"><i class="bi bi-heart fs-4"></i></button>
        </form>
    @else
        <a class="wish-float d-grid place-items-center" href="{{ route('login') }}" aria-label="Login to save {{ $product->name }}"><i class="bi bi-heart fs-4"></i></a>
    @endif
    <div class="mini-label mt-3">{{ $product->category?->name ?? 'Ashvalian' }}</div>
    <h3><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
    <div>
        <span class="price {{ $product->compare_at_price ? 'sale' : '' }}">৳{{ number_format($product->price, 2) }}</span>
        @if ($product->compare_at_price)
            <span class="muted text-decoration-line-through ms-2">৳{{ number_format($product->compare_at_price, 2) }}</span>
        @endif
    </div>
</article>
