@extends('layouts.storefront')

@section('title', 'Shopping Cart | Ashvalian')

@section('content')
    <section class="page-shell section">
        <h1 class="display-4 fw-black">Your Shopping Cart</h1>
        <p class="fs-5 muted">Review your premium selection of luxury fitness essentials.</p>

        <div class="checkout-grid mt-5">
            <div>
                @if ($items->isEmpty())
                    <div class="panel panel-pad">Your bag is empty.</div>
                @else
                    <div class="cart-row table-label d-none d-lg-grid">
                        <span>Product</span><span>Quantity</span><span>Price</span><span>Total</span>
                    </div>
                    @foreach ($items as $row)
                        <div class="cart-row">
                            <div class="cart-product">
                                <img src="{{ $row['product']->primary_image_url }}" alt="{{ $row['product']->name }}">
                                <div>
                                    <div class="mini-label">{{ $row['product']->category?->name }}</div>
                                    <h4 class="fw-black mb-1">{{ $row['product']->name }}</h4>
                                    <p class="muted mb-0">
                                        @foreach ($row['options'] as $key => $value)
                                            {{ $key }}: {{ $value }}@if (! $loop->last) | @endif
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                            <form method="post" action="{{ route('cart.update', $row['product']) }}" class="qty-control">
                                @csrf
                                @method('PATCH')
                                @foreach (($row['options'] ?? []) as $key => $value)
                                    <input type="hidden" name="options[{{ $key }}]" value="{{ $value }}">
                                @endforeach
                                <button class="btn border-0 p-0" name="quantity" value="{{ max(0, $row['quantity'] - 1) }}" aria-label="Decrease quantity">-</button>
                                <span>{{ $row['quantity'] }}</span>
                                <button class="btn border-0 p-0" name="quantity" value="{{ $row['quantity'] + 1 }}" aria-label="Increase quantity">+</button>
                            </form>
                            <div>৳{{ number_format($row['product']->price, 2) }}</div>
                            <strong>৳{{ number_format($row['line_total'], 2) }}</strong>
                        </div>
                    @endforeach
                    <a href="{{ route('category.show', 'fitness') }}" class="text-primary fw-bold mt-4 d-inline-flex gap-2"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
                @endif
            </div>

            <aside class="order-summary panel panel-pad">
                <h2 class="fw-black mb-4">Order Summary</h2>
                <div class="d-flex justify-content-between mb-3"><span>Subtotal</span><strong>৳{{ number_format($subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-3"><span>Shipping Estimate</span><strong class="text-primary">Calculated at next step</strong></div>
                <div class="d-flex justify-content-between mb-4"><span>Estimated Tax</span><strong>৳{{ number_format($tax, 2) }}</strong></div>
                <hr>
                <form method="post" action="{{ route('cart.coupon') }}" class="mb-4" data-ajax-coupon data-target="#coupon-message">
                    @csrf
                    <label class="form-label">Promo Code</label>
                    <div class="d-flex gap-2">
                        <input class="form-control bg-white" name="code" value="{{ $couponCode }}" placeholder="Enter code">
                        <button class="btn-ash" type="submit">Apply</button>
                    </div>
                    <div id="coupon-message" class="small mt-2">@if($discount) Discount applied: ৳{{ number_format($discount, 2) }} @endif</div>
                </form>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h3 class="fw-black">Total</h3>
                    <div class="text-end">
                        <div class="display-6 fw-black">৳{{ number_format($grandTotal, 2) }}</div>
                        <small class="muted">Includes VAT</small>
                    </div>
                </div>
                <a href="{{ route('checkout.index') }}" class="btn-ash btn-blue w-100">Proceed to Checkout <i class="bi bi-arrow-right"></i></a>
                <div class="icon-row justify-content-center mt-4 fs-4 muted">
                    <i class="bi bi-credit-card"></i><i class="bi bi-cash-stack"></i><i class="bi bi-shield-check"></i>
                </div>
            </aside>
        </div>

        <section class="section">
            <div class="section-heading"><h2>You May Also Like</h2></div>
            <div class="product-grid">
                @foreach ($recommendations as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    </section>
@endsection
