@extends('layouts.storefront')

@section('title', 'My Reviews | Ashvalian')

@section('content')
    <section class="page-shell section">
        <h1 class="display-5 fw-black">My Product Reviews</h1>
        <p class="fs-5 muted mb-5">Rate purchased products and edit or delete your own reviews.</p>

        <section class="panel panel-pad mb-5">
            <h2 class="fw-black mb-3">Write a Review</h2>
            <div class="review-list d-grid gap-3">
                @forelse ($purchasedProducts as $product)
                    <form method="post" action="{{ route('customer.reviews.store', $product) }}" class="border-top pt-3">
                        @csrf
                        <div class="d-flex flex-column flex-lg-row gap-3 align-items-start">
                            <img class="rounded-2 flex-shrink-0" style="width:96px;height:96px;object-fit:cover;" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                            <div class="flex-grow-1 w-100">
                                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                    <div>
                                        <strong class="fs-5">{{ $product->name }}</strong>
                                        <div class="text-primary fw-bold">৳{{ number_format($product->price, 2) }}</div>
                                    </div>
                                    <span class="status-pill blue">Review by {{ $product->review_expires_at->format('M j, Y') }}</span>
                                </div>
                                <select class="form-select mb-2" name="rating">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} stars</option>@endfor</select>
                                <input class="form-control mb-2" name="title" placeholder="Review title" required>
                                <textarea class="form-control mb-2" name="body" rows="3" placeholder="Your review" required></textarea>
                                <button class="btn-ash py-2" type="submit">Submit Review</button>
                            </div>
                        </div>
                    </form>
                @empty
                    <div class="border-top pt-3 muted">No delivered products are currently inside the 30-day review window.</div>
                @endforelse
            </div>
        </section>

        <section class="panel panel-pad">
            <h2 class="fw-black mb-3">Your Reviews</h2>
            <div class="review-list d-grid gap-4">
                @forelse ($reviews as $review)
                    <article class="border-top pt-4">
                        <div class="d-flex flex-column flex-xl-row gap-4">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                    <div>
                                        <strong class="fs-5">{{ $review->product?->name }}</strong>
                                        <div class="text-primary">★★★★★ {{ $review->rating }}</div>
                                    </div>
                                    <span class="muted small">{{ $review->created_at->format('M j, Y') }}</span>
                                </div>
                                <h3 class="fs-5 fw-black mb-2">{{ $review->title }}</h3>
                                <p class="muted">{{ $review->body }}</p>
                                <div class="bg-light rounded-2 p-3">
                                    <strong>Manager Reply</strong>
                                    <p class="mb-0 muted">{{ $review->manager_reply ?: 'No reply yet' }}</p>
                                </div>
                            </div>

                            <div class="flex-shrink-0" style="width:min(100%, 340px);">
                                <form method="post" action="{{ route('customer.reviews.update', $review) }}" class="d-grid gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select form-select-sm" name="rating">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected($review->rating == $i)>{{ $i }} stars</option>@endfor</select>
                                    <input class="form-control form-control-sm" name="title" value="{{ $review->title }}" required>
                                    <textarea class="form-control form-control-sm" name="body" rows="2" required>{{ $review->body }}</textarea>
                                    <button class="btn-ghost py-1" type="submit">Save</button>
                                </form>
                                <form method="post" action="{{ route('customer.reviews.destroy', $review) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm text-danger px-0" type="submit"><i class="bi bi-trash"></i> Delete Review</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="border-top pt-3 muted">You have not submitted any product reviews yet.</div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
