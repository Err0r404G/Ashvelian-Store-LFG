@extends('layouts.storefront')

@section('title', 'Ashvalian Store')
@section('body_class', 'market-home-body')

@section('content')
    <div class="market-home">

        {{-- Section 1: Slideable Notice Bar --}}
        @if ($announcements->isNotEmpty())
            <div class="scrolling-notice-bar bg-black py-2 overflow-hidden text-white border-bottom border-dark">
                <marquee scrollamount="5" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
                    @foreach ($announcements as $announcement)
                        <span class="mx-5 d-inline-block">
                            <i class="bi bi-bell-fill text-warning me-2"></i>
                            <strong>{{ $announcement->title }}:</strong> {{ $announcement->message }}
                        </span>
                    @endforeach
                </marquee>
            </div>
        @endif

        {{-- Section 2: Banner Slideable --}}
        @if ($banners->isNotEmpty())
            <div id="homepageBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                @if ($banners->count() > 1)
                    <div class="carousel-indicators">
                        @foreach ($banners as $index => $bannerItem)
                            <button type="button" data-bs-target="#homepageBannerCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : '' }}"></button>
                        @endforeach
                    </div>
                @endif
                
                <div class="carousel-inner">
                    @foreach ($banners as $index => $bannerItem)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <section class="market-hero position-relative overflow-hidden" style="min-height: 550px;">
                                @if ($bannerItem->image_url)
                                    <img src="{{ $bannerItem->image_url }}" alt="{{ $bannerItem->title }}" class="position-absolute inset-0 w-100 h-100 object-fit-cover animate-fade-in" style="opacity: 0.65;">
                                @endif
                                <div class="market-hero-shade position-absolute inset-0"></div>
                                <div class="market-shell market-hero-content position-relative z-1 d-flex flex-column justify-content-center" style="min-height: 550px; padding: 80px 0;">
                                    <span class="market-chip bg-primary text-white border-0 py-1 px-3 rounded-pill fw-bold text-uppercase fs-6 mb-3 d-inline-block" style="width: fit-content;">
                                        {{ $bannerItem->cta_label ?: 'Exclusive collection' }}
                                    </span>
                                    <h1 class="display-3 fw-black text-white mb-3" style="max-width: 750px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        {{ $bannerItem->title }}
                                    </h1>
                                    @if ($bannerItem->subtitle)
                                        <p class="fs-5 text-white-50 mb-4" style="max-width: 600px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                            {{ $bannerItem->subtitle }}
                                        </p>
                                    @endif
                                    @if ($bannerItem->cta_url)
                                        <div class="market-actions">
                                            <a class="btn-ash bg-white text-black border-white px-4 py-3 fw-bold shadow-sm text-decoration-none" href="{{ $bannerItem->cta_url }}">
                                                {{ $bannerItem->cta_label ?: 'Shop Now' }} <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>
                    @endforeach
                </div>

                @if ($banners->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#homepageBannerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#homepageBannerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>
        @else
            <!-- Default Fallback Banner -->
            <section class="market-hero">
                <div class="market-hero-shade"></div>
                <div class="market-shell market-hero-content">
                    <span class="market-chip">Performance Wear</span>
                    <h1>Ashvalian Performance</h1>
                    <p>Premium athletic and fitness collections designed for optimal strength and luxury comfort.</p>
                    <div class="market-actions">
                        <a class="btn-ash bg-white text-black border-white" href="{{ route('shop.index') }}">Explore Shop</a>
                    </div>
                </div>
            </section>
        @endif

        {{-- Section 3: Products on Sale --}}
        <section class="market-section py-5">
            <div class="market-shell">
                <div class="market-section-head mb-4 d-flex justify-content-between align-items-end">
                    <div>
                        <h2 class="fw-black mb-1">Products on Sale</h2>
                        <p class="muted mb-0">Save on premium gear with our exclusive running discount offers.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="text-decoration-none fw-semibold">View All <i class="bi bi-chevron-right fs-6"></i></a>
                </div>
                <div class="market-product-grid">
                    @forelse ($saleProducts as $product)
                        <a class="market-product-card text-decoration-none" href="{{ route('products.show', $product->slug) }}">
                            <div class="market-product-media position-relative overflow-hidden rounded-3 mb-3" style="aspect-ratio: 1/1; background-color: #f8f9fa;">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover transition-transform duration-300 hover-zoom">
                                @if ($product->compare_at_price > $product->price)
                                    @php
                                        $discountPct = round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100);
                                    @endphp
                                    <span class="market-sale-badge bg-danger text-white px-2 py-1 rounded position-absolute top-0 start-0 m-2 fw-bold small">
                                        -{{ $discountPct }}%
                                    </span>
                                @else
                                    <span class="market-sale-badge bg-danger text-white px-2 py-1 rounded position-absolute top-0 start-0 m-2 fw-bold small">
                                        Sale
                                    </span>
                                @endif
                            </div>
                            <div class="mini-label text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem;">{{ $product->category?->name ?? 'Ashvalian' }}</div>
                            <h3 class="h6 text-black fw-bold mb-2">{{ $product->name }}</h3>
                            <div class="market-product-meta d-flex justify-content-between align-items-center">
                                <div class="price-container">
                                    <strong class="text-black">৳{{ number_format($product->price, 2) }}</strong>
                                    @if ($product->compare_at_price)
                                        <span class="text-muted text-decoration-line-through ms-2 small">৳{{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>
                                 @if ($product->rating_count > 0)
                                    <span class="small text-muted"><i class="bi bi-star-fill text-warning me-1"></i> {{ number_format($product->rating_average, 1) }}</span>
                                @else
                                    <span class="small text-muted text-decoration-none">No reviews</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="panel panel-pad text-muted text-center w-100 my-4">No products on sale are currently available.</div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Section 4: Trending Products --}}
        <section class="market-section market-section-soft py-5">
            <div class="market-shell">
                <div class="market-section-head mb-4">
                    <div>
                        <h2 class="fw-black mb-1">Trending Products</h2>
                        <p class="muted mb-0">High-velocity featured picks representing elite fitness design.</p>
                    </div>
                </div>

                @if ($featuredProducts->isNotEmpty())
                    @php
                        $trendingHero = $featuredProducts->first();
                        $trendingOthers = $featuredProducts->slice(1, 3);
                    @endphp
                    <div class="market-bento">
                        <!-- Hero Bento Item -->
                        <a class="market-feature-card text-decoration-none" href="{{ route('products.show', $trendingHero->slug) }}">
                            <div class="market-feature-media position-relative overflow-hidden rounded-3" style="min-height: 320px;">
                                <img src="{{ $trendingHero->primary_image_url }}" alt="{{ $trendingHero->name }}" class="w-100 h-100 object-fit-cover">
                                <span class="position-absolute top-0 start-0 m-3 bg-dark text-white px-3 py-1 rounded-pill small fw-bold">Trending</span>
                            </div>
                            <div class="market-feature-copy p-4">
                                <div>
                                    <div class="mini-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">{{ $trendingHero->category?->name ?? 'Ashvalian' }}</div>
                                    <h3 class="h4 text-black fw-bold mb-2">{{ $trendingHero->name }}</h3>
                                    <p class="text-muted mb-4">{{ \Illuminate\Support\Str::limit($trendingHero->description, 120) }}</p>
                                </div>
                                <strong class="h4 text-black">৳{{ number_format($trendingHero->price, 2) }}</strong>
                            </div>
                        </a>

                        <!-- Side Bento Items -->
                        @foreach ($trendingOthers as $product)
                            <a class="market-bento-card text-decoration-none {{ $loop->last && $trendingOthers->count() < 3 ? 'wide' : '' }}" href="{{ route('products.show', $product->slug) }}">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                <div class="p-3">
                                    <div class="mini-label text-uppercase text-white-50 fw-bold mb-1" style="font-size: 0.7rem;">{{ $product->category?->name ?? 'Ashvalian' }}</div>
                                    <h3 class="h6 text-white fw-bold mb-1">{{ $product->name }}</h3>
                                    <strong class="text-white">৳{{ number_format($product->price, 2) }}</strong>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="panel panel-pad text-muted text-center py-5">Trending products will appear here after the manager selects them.</div>
                @endif
            </div>
        </section>

    </div>
@endsection
