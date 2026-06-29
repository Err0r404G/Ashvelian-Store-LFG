@extends('layouts.storefront')

@section('title', 'Checkout | Ashvalian')

@section('content')
    <section class="page-shell section">
        <div class="mb-5 fs-5">
            <span>Cart</span> <i class="bi bi-chevron-right small"></i>
            <strong>Information</strong> <i class="bi bi-chevron-right small"></i>
            <span>Shipping</span> <i class="bi bi-chevron-right small"></i>
            <span>Payment</span>
        </div>

        <form method="post" action="{{ route('checkout.store') }}" class="checkout-grid">
            @csrf
            <div>
                <h1 class="fw-black mb-4">Shipping Address</h1>
                @if ($addresses->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">Saved Address</label>
                        <select class="form-select" name="address_id">
                            <option value="">Enter a new address</option>
                            @foreach ($addresses as $address)
                                <option value="{{ $address->id }}" @selected(old('address_id') == $address->id || ($address->is_default && ! old('address_id')))>{{ $address->label }} - {{ $address->street_address }}, {{ $address->city }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input class="form-control" name="first_name" value="{{ old('first_name', explode(' ', auth()->user()->name)[0] ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Street Address</label>
                    <input class="form-control" name="street_address" value="{{ old('street_address', 'House 12, Road 4, Sector 7') }}" required>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Delivery Zone</label>
                        <select class="form-select" name="delivery_zone_id" required>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }} - ৳{{ number_format($zone->fee, 2) }} / {{ $zone->estimated_days }} days</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input class="form-control" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
                    </div>
                </div>

                <h2 class="fw-black mt-5 mb-4">Payment Method</h2>
                @foreach ([['sslcommerz', 'Credit / Debit Card', 'Visa, Mastercard, AMEX via SSLCommerz', 'bi-credit-card'], ['bkash', 'bKash', 'Instant mobile wallet payment', 'bi-phone'], ['cod', 'Cash on Delivery', 'Pay when you receive the package', 'bi-cash']] as [$value, $label, $copy, $icon])
                    <label class="panel panel-pad d-flex align-items-center gap-4 mb-3">
                        <i class="bi {{ $icon }} fs-2 text-primary"></i>
                        <span class="flex-grow-1">
                            <strong class="fs-5">{{ $label }}</strong>
                            <span class="d-block muted">{{ $copy }}</span>
                        </span>
                        <input type="radio" class="form-check-input fs-4" name="payment_method" value="{{ $value }}" @checked($loop->first)>
                    </label>
                @endforeach
            </div>

            <aside class="order-summary panel panel-pad">
                <h2 class="fw-black mb-4">Order Summary</h2>
                @foreach ($items as $row)
                    <div class="d-flex gap-3 mb-3">
                        <img class="rounded-2" style="width:92px;height:92px;object-fit:cover;" src="{{ $row['product']->primary_image_url }}" alt="{{ $row['product']->name }}">
                        <div class="flex-grow-1">
                            <strong>{{ $row['product']->name }}</strong>
                            <div class="muted">Qty: {{ $row['quantity'] }}</div>
                            @if (!empty($row['options']))
                                <div class="small text-muted" style="font-size: 0.8rem;">
                                    @foreach ($row['options'] as $key => $value)
                                        {{ $key }}: {{ $value }}@if (! $loop->last) | @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <span>৳{{ number_format($row['line_total'], 2) }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>৳{{ number_format($subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>৳{{ number_format($discount, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><strong class="text-primary">Added after zone</strong></div>
                <div class="d-flex justify-content-between mb-4"><span>Taxes</span><span>৳{{ number_format($tax, 2) }}</span></div>
                <hr>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h3 class="fw-black">Total</h3>
                    <div class="display-6 fw-black">৳{{ number_format($grandTotal, 2) }}</div>
                </div>
                <button class="btn-ash w-100" type="submit">Place Order</button>
                <p class="text-center muted mt-4"><i class="bi bi-lock"></i> Secure SSL encrypted checkout</p>
            </aside>
        </form>
    </section>
@endsection
