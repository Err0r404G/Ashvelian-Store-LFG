@extends('layouts.storefront')

@section('title', 'Wishlist | Ashvalian')

@section('content')
    <section class="page-shell section" style="min-height: 760px;">
        <h1 class="fw-black" style="font-size: 5rem;">My Wishlist</h1>
        <p class="fs-4 muted mb-5">Items you've saved for later.</p>
        <div class="product-grid">
            @forelse ($items as $item)
                <article class="product-card">
                    <a href="{{ route('products.show', $item->product->slug) }}" class="product-card-media">
                        <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}">
                    </a>
                    <button class="wish-float" data-bs-toggle="modal" data-bs-target="#removeWishlist{{ $item->product->id }}" aria-label="Remove item"><i class="bi bi-x-lg fs-4"></i></button>
                    <div class="mini-label mt-3">{{ $item->product->category?->name }}</div>
                    <h3>{{ $item->product->name }}</h3>
                    <div class="price mb-3">৳{{ number_format($item->product->price, 2) }}</div>
                    <form method="post" action="{{ route('cart.add', $item->product) }}">
                        @csrf
                        <button class="btn-ash w-100" type="submit">Move to Bag</button>
                    </form>
                </article>

                <div class="modal fade" id="removeWishlist{{ $item->product->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-2 border-0 p-4">
                            <div class="modal-body text-center">
                                <div class="mx-auto rounded-circle d-grid place-items-center mb-4" style="width:80px;height:80px;background:#ffdada;color:#b10000;"><i class="bi bi-trash fs-3"></i></div>
                                <h2 class="fw-black">Remove Item?</h2>
                                <p class="fs-5 muted">Are you sure you want to remove this item from your wishlist?</p>
                                <div class="d-grid gap-3 mt-4" style="grid-template-columns:1fr 1fr;">
                                    <button class="btn-ghost" data-bs-dismiss="modal" type="button">Cancel</button>
                                    <form method="post" action="{{ route('wishlist.destroy', $item->product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ash w-100" type="submit">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="panel panel-pad">No wishlist items yet.</div>
            @endforelse
        </div>
    </section>
@endsection
