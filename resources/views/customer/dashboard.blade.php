@extends('layouts.storefront')

@section('title', 'Account | Ashvalian')
@section('body_class', 'comfort-mode')
@section('no_footer', true)

@section('content')
    <section class="comfort-dashboard" data-panel-group>
        <aside class="comfort-sidebar">
            @php
                $progress = auth()->user()->membership_progress;
                $tier = auth()->user()->membership_tier;
                
                $bgGradient = match($tier) {
                    'Elite Black' => 'linear-gradient(135deg, #141414 0%, #282828 100%)',
                    'Gold' => 'linear-gradient(135deg, #f3e5ab 0%, #d4af37 100%)',
                    'Silver' => 'linear-gradient(135deg, #f5f5f5 0%, #cccccc 100%)',
                    default => 'linear-gradient(135deg, #ecd5c5 0%, #cd7f32 100%)' // Bronze
                };

                $textColor = match($tier) {
                    'Elite Black' => '#ffffff',
                    'Gold' => '#3a2e00',
                    'Silver' => '#2b2b2b',
                    default => '#4a2c11'
                };

                $subtextColor = match($tier) {
                    'Elite Black' => 'rgba(255, 255, 255, 0.75)',
                    'Gold' => 'rgba(58, 46, 0, 0.75)',
                    'Silver' => 'rgba(43, 43, 43, 0.75)',
                    default => 'rgba(74, 44, 17, 0.75)'
                };
            @endphp
            <div class="panel panel-pad mb-3 shadow-sm border-0 position-relative overflow-hidden" style="background: {{ $bgGradient }}; color: {{ $textColor }}; border-radius: 12px; transition: all 0.2s ease;">
                <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 80%); pointer-events: none;"></div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-uppercase tracking-wider fw-bold" style="font-size: 0.7rem; letter-spacing: 1.5px; opacity: 0.8; color: {{ $subtextColor }};">Membership</div>
                    @if($tier === 'Elite Black')
                        <i class="bi bi-shield-fill-check fs-5" style="color: #d4af37;"></i>
                    @else
                        <i class="bi bi-shield-fill fs-5" style="opacity: 0.8; color: {{ $textColor }};"></i>
                    @endif
                </div>
                
                <h3 class="fw-black mb-3" style="font-size: 1.5rem; letter-spacing: -0.5px; line-height: 1.2;">{{ $tier }}</h3>
                
                @if($progress['next_tier'])
                    <div class="mb-2 d-flex justify-content-between align-items-center" style="font-size: 0.75rem; font-weight: 600;">
                        <span style="color: {{ $subtextColor }};">Next: {{ $progress['next_tier'] }}</span>
                        <span>{{ $progress['percent'] }}%</span>
                    </div>
                    
                    <div class="progress mb-2" style="height: 6px; background-color: rgba(0, 0, 0, 0.08); border-radius: 3px; overflow: hidden; border: none;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $progress['percent'] }}%; background-color: {{ $textColor }}; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 3px; border: none;" 
                             aria-valuenow="{{ $progress['percent'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: {{ $subtextColor }}; font-weight: 500;">
                        <span>৳{{ number_format($progress['current_spent']) }} spent</span>
                        <span>৳{{ number_format($progress['remaining']) }} to go</span>
                    </div>
                @else
                    <div class="d-flex justify-content-between mt-4" style="font-size: 0.75rem; color: {{ $subtextColor }}; font-weight: 500;">
                        <span>Lifetime: ৳{{ number_format($progress['current_spent']) }}</span>
                        <span class="fw-bold"><i class="bi bi-gem"></i> Top Tier Active</span>
                    </div>
                @endif
            </div>
            <div class="panel-switcher d-grid" role="toolbar" aria-label="Account panels">
                <button class="active justify-content-start" type="button" data-panel-target="overview" aria-pressed="true">
                    <i class="bi bi-grid"></i> Overview
                </button>
                <button class="justify-content-start" type="button" data-panel-target="orders" aria-pressed="false">
                    <i class="bi bi-cart"></i> Orders
                </button>
                <button class="justify-content-start" type="button" data-panel-target="wishlist" aria-pressed="false">
                    <i class="bi bi-heart"></i> Wishlist
                </button>
                <button class="justify-content-start" type="button" data-panel-target="support" aria-pressed="false">
                    <i class="bi bi-headset"></i> Support
                </button>
            </div>
            <nav class="portal-nav mt-3">
                <a href="{{ route('customer.profile.edit') }}"><i class="bi bi-person"></i> Profile</a>
                <a href="{{ route('customer.addresses.index') }}"><i class="bi bi-geo-alt"></i> Addresses</a>
                <a href="{{ route('customer.reviews.index') }}"><i class="bi bi-star"></i> Reviews</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
            </nav>
        </aside>

        <main class="comfort-dashboard-main">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="fw-black mb-1">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
                    <p class="muted mb-0">Your orders, support, and saved items are grouped into calm panels.</p>
                </div>
                <a class="btn-ash" href="{{ route('shop.index') }}">Continue Shopping</a>
            </div>

            <div class="comfort-panel-stack">
                <section class="comfort-view" data-panel="overview">
                    <div class="comfort-inner-panel">
                        <div class="comfort-metric-row">
                            <div class="comfort-metric"><div class="table-label">Orders</div><strong>{{ $orders->count() }}</strong></div>
                            <div class="comfort-metric"><div class="table-label">Wishlist</div><strong>{{ $wishlist->count() }}</strong></div>
                            <div class="comfort-metric"><div class="table-label">Tickets</div><strong>{{ $tickets->count() }}</strong></div>
                        </div>
                        @if ($currentOrder)
                            <article class="panel panel-pad shadow-none">
                                <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                                    <div>
                                        <div class="table-label">Current Order</div>
                                        <h2 class="fw-black">{{ $currentOrder->order_number }}</h2>
                                    </div>
                                    <a class="text-primary fw-bold" href="{{ route('orders.invoice', $currentOrder) }}"><i class="bi bi-download"></i> Invoice</a>
                                </div>
                                @foreach ($currentOrder->items->take(1) as $item)
                                    <div class="d-flex gap-4 align-items-center mb-4">
                                        <img class="rounded-2" style="width:88px;height:88px;object-fit:cover;" src="{{ $item->product?->primary_image_url }}" alt="{{ $item->product_name }}">
                                        <div>
                                            <h3 class="fw-black mb-1">{{ $item->product_name }}</h3>
                                            <p class="muted mb-0">Qty: {{ $item->quantity }} - {{ strtoupper(str_replace('_', ' ', $currentOrder->status)) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="d-grid text-center" style="grid-template-columns: repeat(4, 1fr);">
                                    @foreach (['Pending', 'Confirmed', 'Shipped', 'Delivered'] as $step)
                                        <div class="{{ strtolower($step) === $currentOrder->status || ($step === 'Shipped' && in_array($currentOrder->status, ['shipped','out_for_delivery'])) ? 'text-primary fw-bold' : 'muted' }}">
                                            <i class="bi bi-circle-fill"></i>
                                            <div class="table-label mt-2">{{ $step }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @else
                            <div class="panel panel-pad shadow-none">No active order yet.</div>
                        @endif
                    </div>
                </section>

                <section class="comfort-view" data-panel="orders" hidden>
                    <div class="comfort-inner-panel">
                        <div class="comfort-panel-title">
                            <div>
                                <div class="mini-label">Order History</div>
                                <h2>Past transactions</h2>
                            </div>
                        </div>
                        <table class="compact-table">
                            <thead><tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td><strong>{{ $order->order_number }}</strong></td>
                                        <td>{{ $order->placed_at?->format('M d, Y') }}</td>
                                        <td><strong>৳{{ number_format($order->grand_total, 2) }}</strong></td>
                                        <td><span class="status-pill green">{{ $order->status }}</span></td>
                                        <td><a class="text-primary fw-bold" href="{{ route('orders.show', $order) }}">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="comfort-view" data-panel="wishlist" hidden>
                    <div class="comfort-inner-panel">
                        <div class="comfort-panel-title">
                            <div>
                                <div class="mini-label">Saved Items</div>
                                <h2>Wishlist highlights</h2>
                            </div>
                            <a class="btn-ghost" href="{{ route('wishlist.index') }}">Open Wishlist</a>
                        </div>
                        <div class="comfort-product-grid">
                            @forelse ($wishlist as $item)
                                <a class="comfort-product-mini" href="{{ route('products.show', $item->product->slug) }}">
                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}">
                                    <div class="mini-body">
                                        <div class="mini-label">{{ $item->product->category?->name }}</div>
                                        <h3>{{ $item->product->name }}</h3>
                                        <strong>৳{{ number_format($item->product->price, 2) }}</strong>
                                    </div>
                                </a>
                            @empty
                                <div class="panel panel-pad shadow-none">No wishlist items yet.</div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="comfort-view" data-panel="support" hidden>
                    <div class="comfort-inner-panel">
                        <div class="comfort-panel-title">
                            <div>
                                <div class="mini-label">Concierge Support</div>
                                <h2>Create a ticket</h2>
                            </div>
                        </div>
                        <form method="post" action="{{ route('support.tickets.store') }}" class="row g-3">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label">Issue Category</label>
                                <select class="form-select" name="category">
                                    <option>Order Tracking</option>
                                    <option>Returns</option>
                                    <option>Payment</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Subject</label>
                                <input class="form-control" name="subject" placeholder="What do you need help with?" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="message" rows="5" placeholder="How can we assist you?" required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn-ash" type="submit">Submit Ticket</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </section>
@endsection
