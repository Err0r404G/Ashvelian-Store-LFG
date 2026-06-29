@php
    $navCategories = \App\Models\Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get();
    $cartCount = collect(session('cart.items', []))->sum('quantity');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | Ashvalian</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ashvalian.css') }}?v={{ filemtime(public_path('css/ashvalian.css')) }}">
    <style>
        .demo-login-btn {
            background: #fff;
            border: 1px solid var(--ash-line);
            color: var(--ash-black);
            border-radius: 6px;
            padding: 10px;
            font-size: 0.85rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .demo-login-btn:hover {
            background: var(--ash-soft);
            border-color: var(--ash-black);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 20, 24, 0.08);
        }
    </style>
</head>
<body class="auth-page">
    <header class="site-header">
        <a href="{{ route('home') }}" class="brand">Ashvalian</a>
        <nav class="site-nav">
            <a href="{{ route('shop.index') }}">Shop</a>
            @foreach ($navCategories as $category)
                <a href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
        <div class="site-actions">
            <form action="{{ route('shop.index') }}" method="get" class="d-none d-lg-block">
                <input class="search-pill" name="q" placeholder="Search performance..." aria-label="Search products">
            </form>
            <a class="icon-link" href="{{ route('login') }}" aria-label="Wishlist">
                <i class="bi bi-heart fs-4"></i>
            </a>
            <a class="icon-link" href="{{ route('cart.index') }}" aria-label="Shopping bag">
                <i class="bi bi-bag fs-4"></i>
                @if ($cartCount > 0)<span class="badge-dot">{{ $cartCount }}</span>@endif
            </a>
            <a href="{{ route('home') }}" class="d-none d-xl-inline">Home</a>
            <a href="#register" class="btn-ash py-2">Register</a>
        </div>
    </header>

    <main class="text-center">
        <h1 class="display-4 fw-black mt-5 mb-2">Welcome Back</h1>
        <p class="fs-4 muted">The intersection of technical excellence and luxury.</p>

        <section class="auth-card text-start">
            @include('partials.flash')
            <div class="auth-tabs">
                <button id="loginTab" class="active" type="button">Login</button>
                <button id="registerTab" type="button">Register</button>
            </div>

            <form id="loginPanel" method="post" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text border-0"><i class="bi bi-envelope"></i></span>
                        <input class="form-control" name="email" type="email" value="{{ old('email', 'customer@ashvalian.test') }}" placeholder="customer@ashvalian.test" required>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                        <a class="text-primary small fw-bold text-decoration-none" href="{{ route('password.forgot') }}">Forgot password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text border-0"><i class="bi bi-lock"></i></span>
                        <input class="form-control" name="password" type="password" value="Password123!" required>
                    </div>
                </div>
                <button class="btn-ash w-100 mb-4" type="submit">Sign In <i class="bi bi-arrow-right"></i></button>

                <div class="d-flex align-items-center mb-3">
                    <hr class="flex-grow-1 border-secondary-subtle my-0">
                    <span class="px-3 muted fw-bold text-uppercase fs-7" style="font-size: 0.72rem; letter-spacing: 0.05em; color: var(--ash-muted);">Demo Login</span>
                    <hr class="flex-grow-1 border-secondary-subtle my-0">
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <button type="button" class="demo-login-btn w-100 d-flex flex-column align-items-center text-center" onclick="quickLogin('admin@ashvalian.test')">
                            <span class="fw-bold text-uppercase" style="font-size: 0.75rem;">Admin</span>
                            <span class="text-muted" style="font-size: 0.65rem;">admin@ashvalian.test</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="demo-login-btn w-100 d-flex flex-column align-items-center text-center" onclick="quickLogin('manager@ashvalian.test')">
                            <span class="fw-bold text-uppercase" style="font-size: 0.75rem;">Manager</span>
                            <span class="text-muted" style="font-size: 0.65rem;">manager@ashvalian.test</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="demo-login-btn w-100 d-flex flex-column align-items-center text-center" onclick="quickLogin('delivery@ashvalian.test')">
                            <span class="fw-bold text-uppercase" style="font-size: 0.75rem;">Delivery</span>
                            <span class="text-muted" style="font-size: 0.65rem;">delivery@ashvalian.test</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="demo-login-btn w-100 d-flex flex-column align-items-center text-center" onclick="quickLogin('customer@ashvalian.test')">
                            <span class="fw-bold text-uppercase" style="font-size: 0.75rem;">Customer</span>
                            <span class="text-muted" style="font-size: 0.65rem;">customer@ashvalian.test</span>
                        </button>
                    </div>
                </div>
            </form>

            <form id="registerPanel" class="d-none" method="post" action="{{ route('register.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                    <div class="small muted mt-2">We will send an OTP here before creating your account.</div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input class="form-control" name="password" type="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm</label>
                        <input class="form-control" name="password_confirmation" type="password" required>
                    </div>
                </div>
                <button class="btn-ash w-100" type="submit">Send OTP</button>
            </form>

            <div class="text-center my-4">
                <span class="muted fw-bold">Or continue with</span>
            </div>
            <div class="d-grid gap-3">
                <a class="btn-ghost text-center text-decoration-none" href="{{ route('auth.google.redirect') }}"><i class="bi bi-google"></i> Continue with Google</a>
            </div>
            <hr class="my-4">
            <div class="d-flex justify-content-center gap-4 muted">
                <span><i class="bi bi-shield-check"></i> 256-bit Encryption</span>
                <span><i class="bi bi-shield-lock"></i> Privacy Guaranteed</span>
            </div>
        </section>

        <p class="fs-5">By accessing Ashvalian, you agree to our <strong>Terms of Service</strong> and <strong>Privacy Policy</strong>.</p>
    </main>

    <footer class="text-center py-5 mt-5 bg-light muted">© {{ date('Y') }} Ashvalian Luxury Fitness. All rights reserved.</footer>

    <script>
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        function showRegister() {
            loginTab.classList.remove('active');
            registerTab.classList.add('active');
            loginPanel.classList.add('d-none');
            registerPanel.classList.remove('d-none');
        }
        function showLogin() {
            registerTab.classList.remove('active');
            loginTab.classList.add('active');
            registerPanel.classList.add('d-none');
            loginPanel.classList.remove('d-none');
        }
        registerTab.addEventListener('click', showRegister);
        loginTab.addEventListener('click', showLogin);
        function showPanelFromHash() {
            if (window.location.hash === '#register') {
                showRegister();
            }
        }
        showPanelFromHash();
        window.addEventListener('hashchange', showPanelFromHash);

        function quickLogin(email) {
            const emailInput = document.querySelector('#loginPanel input[name="email"]');
            const passwordInput = document.querySelector('#loginPanel input[name="password"]');
            if (emailInput && passwordInput) {
                emailInput.value = email;
                passwordInput.value = 'Password123!';
                document.getElementById('loginPanel').submit();
            }
        }
    </script>
</body>
</html>
