@php
    $user = auth()->user();
    $isDelivery = in_array($user?->role, ['delivery_manager'], true);
    $portalRoleLabel = match ($user?->role) {
        'admin' => 'Admin',
        'manager' => 'Manager',
        'delivery_manager' => 'Delivery',
        default => 'Portal',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ashvalian Portal')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ashvalian.css') }}?v={{ filemtime(public_path('css/ashvalian.css')) }}">
</head>
<body>
    <div class="portal-shell">
        <aside class="portal-sidebar">
            <div class="brand">Ashvalian {{ $portalRoleLabel }}</div>
            <p class="muted mb-0">{{ $isDelivery ? 'Logistics Portal' : 'Management Portal' }}</p>
            <nav class="portal-nav">
                @if ($user?->isAdmin())
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-bar-chart"></i> Analytics</a>
                    <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Users</a>
                    <a class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.create') }}"><i class="bi bi-person-plus"></i> Create Staff</a>
                    <a class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}"><i class="bi bi-ticket-perforated"></i> Coupons</a>
                    <a class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}"><i class="bi bi-megaphone"></i> Announcements</a>
                    <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
                @endif
                @if ($user?->isManager())
                    <a class="{{ request()->routeIs('manager.homepage.*') ? 'active' : '' }}" href="{{ route('manager.homepage.index') }}"><i class="bi bi-layout-text-window"></i> Homepage</a>
                    <a class="{{ request()->routeIs('manager.products.*') ? 'active' : '' }}" href="{{ route('manager.products.index') }}"><i class="bi bi-box"></i> Products</a>
                    <a class="{{ request()->routeIs('manager.categories.*') ? 'active' : '' }}" href="{{ route('manager.categories.index') }}"><i class="bi bi-tags"></i> Categories</a>
                    <a class="{{ request()->routeIs('manager.delivery-zones.*') ? 'active' : '' }}" href="{{ route('manager.delivery-zones.index') }}"><i class="bi bi-geo-alt"></i> Delivery Zones</a>
                    <a class="{{ request()->routeIs('manager.reviews.*') ? 'active' : '' }}" href="{{ route('manager.reviews.index') }}"><i class="bi bi-star"></i> Reviews</a>
                    <a class="{{ request()->routeIs('manager.returns.*') ? 'active' : '' }}" href="{{ route('manager.returns.index') }}"><i class="bi bi-arrow-counterclockwise"></i> Return Requests</a>
                @endif
                @if ($isDelivery)
                    <a class="{{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}" href="{{ route('delivery.dashboard') }}"><i class="bi bi-cart"></i> Orders</a>
                    <a class="{{ request()->routeIs('delivery.incoming') ? 'active' : '' }}" href="{{ route('delivery.incoming') }}"><i class="bi bi-inboxes"></i> Incoming Items</a>
                    <a class="{{ request()->routeIs('delivery.dispatch') ? 'active' : '' }}" href="{{ route('delivery.dispatch') }}"><i class="bi bi-box-seam"></i> Ready Dispatch</a>
                    <a class="{{ request()->routeIs('delivery.active') ? 'active' : '' }}" href="{{ route('delivery.active') }}"><i class="bi bi-truck"></i> Active Deliveries</a>
                    <a class="{{ request()->routeIs('delivery.returns') ? 'active' : '' }}" href="{{ route('delivery.returns') }}"><i class="bi bi-arrow-counterclockwise"></i> Returns</a>
                    <a class="{{ request()->routeIs('delivery.history') ? 'active' : '' }}" href="{{ route('delivery.history') }}"><i class="bi bi-clock-history"></i> History</a>
                    <a class="{{ request()->routeIs('delivery.summary') ? 'active' : '' }}" href="{{ route('delivery.summary') }}"><i class="bi bi-calendar-week"></i> Summary</a>
                @endif
            </nav>
            <div class="portal-nav mt-auto position-absolute bottom-0 start-0 end-0 px-4 pb-5">
                <a class="{{ request()->routeIs('portal.profile.*') ? 'active' : '' }}" href="{{ route('portal.profile.edit') }}"><i class="bi bi-gear"></i> Profile Settings</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
            </div>
        </aside>
        <main class="portal-main">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
