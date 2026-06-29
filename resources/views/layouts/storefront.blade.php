@php
    $navCategories = \App\Models\Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get();
    $cartCount = auth()->check()
        ? optional(\App\Models\Cart::where('user_id', auth()->id())->with('items')->first())->items?->sum('quantity') ?? 0
        : collect(session('cart.items', []))->sum('quantity');
    $wishlistCount = auth()->check() ? \App\Models\Wishlist::where('user_id', auth()->id())->count() : 0;
    $accountRoute = auth()->check() ? match (auth()->user()->role) {
        'admin' => route('admin.dashboard'),
        'manager' => route('manager.products.index'),
        'delivery_manager' => route('delivery.dashboard'),
        default => route('customer.dashboard'),
    } : route('login');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ashvalian.css') }}?v={{ filemtime(public_path('css/ashvalian.css')) }}">
</head>
<body class="@yield('body_class')">
    <header class="site-header">
        <a href="{{ route('home') }}" class="brand">Ashvalian</a>
        <nav class="site-nav">
            @foreach ($navCategories as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="{{ request()->is('shop/'.$category->slug) ? 'active' : '' }}">{{ $category->name }}</a>
            @endforeach
        </nav>
        <div class="site-actions">
            <form action="{{ route('shop.index') }}" method="get" class="d-none d-lg-block">
                <input class="search-pill" name="q" value="{{ request('q') }}" placeholder="Search performance..." aria-label="Search products">
            </form>
            <a class="icon-link" href="{{ auth()->check() && auth()->user()->isCustomer() ? route('wishlist.index') : route('login') }}" aria-label="Wishlist">
                <i class="bi bi-heart fs-4"></i>
                @if ($wishlistCount > 0)<span class="badge-dot">{{ $wishlistCount }}</span>@endif
            </a>
            <a class="icon-link" href="{{ route('cart.index') }}" aria-label="Shopping bag">
                <i class="bi bi-bag fs-4"></i>
                @if ($cartCount > 0)<span class="badge-dot">{{ $cartCount }}</span>@endif
            </a>
            @auth
                <a href="{{ $accountRoute }}" class="icon-link" aria-label="Account"><i class="bi bi-person fs-3"></i></a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('login') }}#register" class="btn-ash py-2">Register</a>
            @endauth
        </div>
    </header>

    <main>
        <div class="page-shell pt-3">
            @include('partials.flash')
        </div>
        @yield('content')
    </main>

    @unless(View::hasSection('no_footer'))
        <footer class="site-footer">
            <div class="footer-grid page-shell">
                <div>
                    <div class="brand mb-3">Ashvalian</div>
                    <p class="muted">Elevating human performance through luxury engineering and sophisticated design.</p>
                    <div class="icon-row mt-3">
                        <i class="bi bi-globe2"></i>
                        <i class="bi bi-share"></i>
                        <i class="bi bi-instagram"></i>
                    </div>
                </div>
                <div>
                    <strong>Explore</strong>
                    <p class="mt-3 mb-2"><a href="{{ route('category.show', 'fitness') }}">Fitness</a></p>
                    <p class="mb-2"><a href="{{ route('category.show', 'fashion') }}">Fashion</a></p>
                    <p><a href="{{ route('category.show', 'accessories') }}">Accessories</a></p>
                </div>
                <div>
                    <strong>Support</strong>
                    <p class="mt-3 mb-2">Contact Support</p>
                    <p class="mb-2">Privacy Policy</p>
                    <p>Shipping & Returns</p>
                </div>
                <div>
                    <strong>Stay Elite</strong>
                    <p class="muted mt-3">Sign up for exclusive releases and training insights.</p>
                    <form class="d-flex gap-2">
                        <input class="form-control bg-white" placeholder="Your email" aria-label="Email">
                        <button class="btn-ash" type="button"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            </div>
            <div class="page-shell pt-4 mt-4 border-top muted">© {{ date('Y') }} Ashvalian Luxury Fitness. All rights reserved.</div>
        </footer>
    @endunless

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/ashvalian.js') }}?v={{ filemtime(public_path('js/ashvalian.js')) }}"></script>
    @stack('scripts')
</body>
</html>
