@extends('layouts.storefront')

@section('title', $product->name.' | Ashvalian')

@section('content')
    <style>
        .option-btn-label {
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .option-btn-label:hover {
            background-color: #f8f9fa !important;
            border-color: #000 !important;
        }
        .option-btn-label:has(input[type="radio"]:checked) {
            background-color: #000 !important;
            color: #fff !important;
            border-color: #000 !important;
        }
        .thumbnail-img {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        .thumbnail-img.active {
            border-color: #000 !important;
            transform: scale(1.03);
        }
        .thumbnail-img:hover {
            opacity: 0.8;
        }
    </style>
    <section class="page-shell section">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="product-card-media" style="aspect-ratio: 1 / 1.05;">
                    <img id="main-product-image" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                </div>
                <div class="row g-3 mt-2" id="thumbnail-gallery">
                    <div class="col-3">
                        <img class="rounded-2 w-100 thumbnail-img active" style="aspect-ratio:1/1;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                    </div>
                    @foreach (array_slice($product->images ?? [], 0, 3) as $image)
                        <div class="col-3">
                            <img class="rounded-2 w-100 thumbnail-img" style="aspect-ratio:1/1;object-fit:cover;" src="{{ $image }}" alt="{{ $product->name }} detail">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5">
                <div class="mini-label">{{ $product->category->name }}</div>
                <h1 class="display-5 fw-black">{{ $product->name }}</h1>
                <div class="d-flex gap-2 align-items-center mb-3">
                    @if ($product->rating_count > 0)
                        <span class="text-primary">
                            @for ($i = 1; $i <= 5; $i++)
                                {!! $i <= round($product->rating_average) ? '★' : '☆' !!}
                            @endfor
                        </span>
                        <span>{{ number_format($product->rating_average, 1) }}</span>
                        <span class="muted">({{ $product->rating_count }} {{ \Illuminate\Support\Str::plural('review', $product->rating_count) }})</span>
                    @else
                        <span class="text-muted">☆☆☆☆☆</span>
                        <span class="muted">No reviews yet</span>
                    @endif
                </div>
                <h3 class="fw-black mb-4">৳{{ number_format($product->price, 2) }}</h3>
                <div class="d-flex gap-2 flex-wrap mb-4">
                    <span class="status-pill blue">Seller: {{ $product->creator?->name ?? 'Ashvalian Store' }}</span>
                    <span class="status-pill {{ $product->stock_quantity > 0 ? 'green' : 'red' }}">
                        {{ $product->stock_quantity > 0 ? $product->stock_quantity.' in stock' : 'Out of stock' }}
                    </span>
                </div>
                <p class="fs-5 muted">{{ $product->description }}</p>

                <form method="post" action="{{ route('cart.add', $product) }}" class="mt-4">
                    @csrf
                    @if ($product->colors)
                        <div class="mb-4">
                            <label class="form-label">Color</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($product->colors as $color)
                                    <label class="btn-ghost py-2 option-btn-label">
                                        <input type="radio" class="d-none" name="color" value="{{ $color }}" @checked($loop->first)> {{ $color }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($product->sizes)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Select Size</label>
                                @if ($product->has_size_guide && $product->size_guide_image)
                                    <a href="{{ route('products.size-guide', $product->slug) }}" target="_blank" class="small text-primary fw-bold text-decoration-none">Size Guide</a>
                                @endif
                            </div>
                            <div class="d-grid gap-2" style="grid-template-columns: repeat(5, 1fr);">
                                @foreach ($product->sizes as $size)
                                    <label class="btn-ghost py-2 option-btn-label">
                                        <input type="radio" class="d-none" name="size" value="{{ $size }}" @checked($loop->first)> {{ $size }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <button class="btn-ash w-100 mb-3" type="submit">Add to Bag</button>
                </form>

                @auth
                    <form method="post" action="{{ route('wishlist.toggle', $product) }}">
                        @csrf
                        <button class="btn-ghost w-100" type="submit"><i class="bi bi-heart"></i> Add to Wishlist</button>
                    </form>
                @endauth

                <div class="border-top mt-5 pt-4">
                    <h5 class="fw-black">Technical Specifications</h5>
                    @foreach (($product->specifications ?? []) as $key => $value)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="muted">{{ $key }}</span>
                            <strong>{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>



    <section class="section page-shell" id="productReviewsSection">
        <div class="section-heading">
            <div>
                <h2>Customer Stories</h2>
                @if ($product->rating_count > 0)
                    <p class="muted">{{ number_format($product->rating_average, 1) }} based on {{ $product->rating_count }} {{ \Illuminate\Support\Str::plural('review', $product->rating_count) }}</p>
                @else
                    <p class="muted">No reviews yet</p>
                @endif
            </div>
            @if ($canReview)
                <!-- Write/Update Review Button -->
                <button type="button" class="btn-ash" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                    {{ $existingReview ? 'Update Your Review' : 'Write a Review' }}
                </button>

                <!-- Review Modal -->
                <div class="modal fade text-start" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('customer.reviews.store', $product->id) }}" method="post" class="modal-content text-dark">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title fw-black" id="writeReviewModalLabel">{{ $existingReview ? 'Update Your Review' : 'Write a Review' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Rating *</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="">Select stars...</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" @selected($existingReview && $existingReview->rating == $i)>{{ str_repeat('★', $i) }} ({{ $i }} Stars)</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Review Title *</label>
                                    <input type="text" name="title" class="form-control" value="{{ $existingReview?->title }}" placeholder="e.g. Excellent quality and fit!" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Review Content *</label>
                                    <textarea name="body" class="form-control" rows="4" placeholder="Tell us more about your experience with this product..." required>{{ $existingReview?->body }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-ash">Submit Review</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-4 mt-2">
            <div class="col-lg-8 mx-auto">
                @forelse ($product->reviews as $review)
                    <div class="panel panel-pad mb-3 border-light shadow-sm text-dark bg-white">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="text-primary fs-5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        {!! $i <= $review->rating ? '★' : '☆' !!}
                                    @endfor
                                </div>
                                <h5 class="fw-black mt-1 mb-0 text-black">"{{ $review->title }}"</h5>
                            </div>
                            <span class="small text-muted">{{ $review->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="fst-italic text-muted mb-2">{{ $review->body }}</p>
                        <div class="small">By <strong>{{ $review->user->name }}</strong></div>

                        {{-- Manager Reply Display --}}
                        @if ($review->manager_reply)
                            <div class="mt-3 p-3 bg-light rounded border-start border-dark border-3 text-dark">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-patch-check-fill text-dark me-2"></i>
                                    <strong>Support</strong>
                                </div>
                                <p class="mb-0 text-muted fst-italic">{{ $review->manager_reply }}</p>
                            </div>
                        @endif

                        {{-- Manager Reply Form --}}
                        @if (auth()->check() && auth()->user()->isManager())
                            <div class="mt-3 border-top pt-3">
                                <form action="{{ route('manager.reviews.update', $review->id) }}" method="post">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_approved" value="1">
                                    <input type="hidden" name="is_featured" value="{{ $review->is_featured ? '1' : '0' }}">
                                    <div class="form-group mb-2">
                                        <label class="form-label small fw-bold text-dark">
                                            {{ $review->manager_reply ? 'Update Support Reply:' : 'Reply to this review as Support:' }}
                                        </label>
                                        <textarea name="manager_reply" class="form-control" rows="2" placeholder="e.g. Thank you for your feedback! We are glad you liked it." required>{{ $review->manager_reply }}</textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-sm btn-ash px-4">
                                            {{ $review->manager_reply ? 'Update Reply' : 'Submit Reply' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="panel panel-pad text-muted text-center py-5">
                        No reviews yet for this product. Be the first to share your experience!
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section page-shell">
        <div class="section-heading"><h2>You May Also Like</h2></div>
        <div class="product-grid">
            @foreach ($relatedProducts as $related)
                @include('partials.product-card', ['product' => $related])
            @endforeach
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const variantStock = @json($product->variant_stock ?? null);
            const colorInputs = document.querySelectorAll('input[name="color"]');
            const sizeInputs = document.querySelectorAll('input[name="size"]');
            const defaultImage = @json($product->primary_image_url);
            const mainImgElement = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-img');

            let currentImageIndex = 0;
            let slideInterval = null;

            function highlightThumbnail(index) {
                if (index < 0 || index >= thumbnails.length) return;
                const thumb = thumbnails[index];
                
                if (mainImgElement) {
                    mainImgElement.src = thumb.src;
                }

                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                
                currentImageIndex = index;
            }

            function startAutoSlide() {
                if (thumbnails.length <= 1) return;
                stopAutoSlide();
                slideInterval = setInterval(() => {
                    let nextIndex = (currentImageIndex + 1) % thumbnails.length;
                    highlightThumbnail(nextIndex);
                }, 4000);
            }

            function stopAutoSlide() {
                if (slideInterval) {
                    clearInterval(slideInterval);
                    slideInterval = null;
                }
            }

            function syncActiveThumbnail(url) {
                let found = false;
                thumbnails.forEach((thumb, index) => {
                    if (thumb.src === url) {
                        thumbnails.forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                        currentImageIndex = index;
                        found = true;
                    }
                });

                if (!found) {
                    thumbnails.forEach(t => t.classList.remove('active'));
                    stopAutoSlide(); // Pause auto slide for custom variant images
                } else {
                    startAutoSlide(); // Restart/Resume auto-slide for gallery images
                }
            }

            // Thumbnail click event listeners
            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    highlightThumbnail(index);
                    startAutoSlide(); // Reset slide interval
                });
            });

            // Start auto slide initially
            startAutoSlide();

            // Variant stock specific features
            if (variantStock) {
                function getStockQty(key) {
                    const stockVal = variantStock[key];
                    if (stockVal === undefined) return 0;
                    if (typeof stockVal === 'object' && stockVal !== null) {
                        return stockVal.qty !== undefined ? stockVal.qty : 0;
                    }
                    return parseInt(stockVal, 10) || 0;
                }

                function updateProductImage() {
                    if (!mainImgElement) return;

                    const checkedColorInput = document.querySelector('input[name="color"]:checked');
                    const checkedSizeInput = document.querySelector('input[name="size"]:checked');
                    
                    if (!checkedColorInput) {
                        mainImgElement.src = defaultImage;
                        syncActiveThumbnail(defaultImage);
                        return;
                    }

                    const color = checkedColorInput.value;
                    const size = checkedSizeInput ? checkedSizeInput.value : null;

                    // 1. Try exact combination (Color - Size)
                    if (size) {
                        const key = `${color} - ${size}`;
                        const val = variantStock[key];
                        if (val && typeof val === 'object' && val.image) {
                            mainImgElement.src = val.image;
                            syncActiveThumbnail(val.image);
                            return;
                        }
                    }

                    // 2. Try any combination for the selected color
                    for (const key in variantStock) {
                        if (key.startsWith(`${color} - `)) {
                            const val = variantStock[key];
                            if (val && typeof val === 'object' && val.image) {
                                mainImgElement.src = val.image;
                                syncActiveThumbnail(val.image);
                                return;
                            }
                        }
                    }

                    // 3. Fall back to default
                    mainImgElement.src = defaultImage;
                    syncActiveThumbnail(defaultImage);
                }

                function updateSizeAvailability() {
                    const checkedColorInput = document.querySelector('input[name="color"]:checked');
                    if (!checkedColorInput) return;
                    const color = checkedColorInput.value;

                    sizeInputs.forEach(input => {
                        const size = input.value;
                        const key = `${color} - ${size}`;
                        const stock = getStockQty(key);
                        const label = input.closest('label');

                        if (stock <= 0) {
                            input.disabled = true;
                            label.style.opacity = '0.35';
                            label.style.pointerEvents = 'none';
                            label.setAttribute('title', 'Out of stock');
                            if (input.checked) {
                                input.checked = false;
                            }
                        } else {
                            input.disabled = false;
                            label.style.opacity = '1';
                            label.style.pointerEvents = 'auto';
                            label.removeAttribute('title');
                        }
                    });

                    const checkedSizeInput = document.querySelector('input[name="size"]:checked');
                    const submitBtn = document.querySelector('button[type="submit"].btn-ash');
                    
                    if (!checkedSizeInput) {
                        const firstAvailable = Array.from(sizeInputs).find(input => !input.disabled);
                        if (firstAvailable) {
                            firstAvailable.checked = true;
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerText = 'Add to Bag';
                            }
                        } else {
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.innerText = 'Out of Stock';
                            }
                        }
                    } else {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Add to Bag';
                        }
                    }
                }

                if (colorInputs.length > 0) {
                    colorInputs.forEach(input => {
                        input.addEventListener('change', function() {
                            updateSizeAvailability();
                            updateProductImage();
                        });
                    });
                }

                if (sizeInputs.length > 0) {
                    sizeInputs.forEach(input => {
                        input.addEventListener('change', updateProductImage);
                    });
                }

                // Initial run of variant stock dependent state
                updateSizeAvailability();
                updateProductImage();
            }
        });
    </script>
@endsection
