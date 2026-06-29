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
    <title>Verify OTP | Ashvalian</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ashvalian.css') }}?v={{ filemtime(public_path('css/ashvalian.css')) }}">
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
            <a class="icon-link" href="{{ route('cart.index') }}" aria-label="Shopping bag">
                <i class="bi bi-bag fs-4"></i>
                @if ($cartCount > 0)<span class="badge-dot">{{ $cartCount }}</span>@endif
            </a>
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </header>

    <main class="text-center">
        <h1 class="display-4 fw-black mt-5 mb-2">Verify OTP</h1>
        <p class="fs-4 muted">Enter the 6-digit code sent to {{ $pendingRegistration->destination }}.</p>

        <section class="auth-card text-start">
            @include('partials.flash')

            @if ($demoOtp)
                <div class="alert alert-info">
                    Demo OTP: <strong class="fs-5">{{ $demoOtp }}</strong>
                </div>
            @endif

            <form method="post" action="{{ route('register.verify.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Verification Code</label>
                    <input class="form-control text-center fs-3 fw-black" name="otp" inputmode="numeric" maxlength="6" placeholder="000000" required autofocus>
                    <div class="small muted mt-2">The code expires at {{ $pendingRegistration->expires_at->format('h:i A') }}.</div>
                </div>
                <button class="btn-ash w-100" type="submit">Verify & Create Account</button>
            </form>

            <form method="post" action="{{ route('register.resend') }}" class="mt-3">
                @csrf
                <button class="btn-ghost w-100" type="submit"><i class="bi bi-arrow-clockwise"></i> Resend OTP</button>
            </form>
        </section>
    </main>
</body>
</html>
