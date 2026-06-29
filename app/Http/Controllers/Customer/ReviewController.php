<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        return view('customer.reviews', [
            'reviews' => Review::with(['product', 'order'])->where('user_id', $request->user()->id)->latest()->get(),
            'purchasedProducts' => $this->reviewableProductsFor($request),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();
        $reviewableDelivery = $this->latestReviewableDelivery($request, $product);

        if (! $existingReview && ! $reviewableDelivery) {
            throw ValidationException::withMessages([
                'product' => 'The 30-day review window has expired or this product has not been delivered yet.',
            ]);
        }

        $data = $this->validated($request);

        $review = Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            $data + [
                'order_id' => $reviewableDelivery?->order_id ?? $existingReview?->order_id,
                'is_approved' => true,
            ]
        );

        $this->refreshProductRating($product);

        return back()->with('status', $review->wasRecentlyCreated ? 'Review submitted.' : 'Review updated.');
    }

    public function update(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $review->update($this->validated($request));
        $this->refreshProductRating($review->product);

        return back()->with('status', 'Review updated.');
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $product = $review->product;
        $review->delete();
        $this->refreshProductRating($product);

        return back()->with('status', 'Review deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1200'],
        ]);
    }

    private function refreshProductRating(Product $product): void
    {
        $product->update([
            'rating_average' => round((float) $product->reviews()->where('is_approved', true)->avg('rating'), 2),
            'rating_count' => $product->reviews()->where('is_approved', true)->count(),
        ]);
    }

    private function reviewableProductsFor(Request $request): Collection
    {
        $reviewedProductIds = Review::where('user_id', $request->user()->id)->pluck('product_id');

        return OrderItem::with(['order', 'product'])
            ->whereNotNull('product_id')
            ->whereNotIn('product_id', $reviewedProductIds)
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'delivered')
                    ->whereNotNull('delivered_at');
            })
            ->get()
            ->groupBy('product_id')
            ->map(function (Collection $items) {
                $latestItem = $items
                    ->sortByDesc(fn (OrderItem $item) => $this->receivedAt($item)?->timestamp ?? 0)
                    ->first();

                $receivedAt = $this->receivedAt($latestItem);

                if (! $receivedAt || $receivedAt->lt(now()->subDays(30))) {
                    return null;
                }

                $product = $latestItem->product;
                $product->review_order_id = $latestItem->order_id;
                $product->review_delivered_at = $receivedAt;
                $product->review_expires_at = $receivedAt->copy()->addDays(30);

                return $product;
            })
            ->filter()
            ->values();
    }

    private function latestReviewableDelivery(Request $request, Product $product): ?OrderItem
    {
        $latestItem = $product->orderItems()
            ->with('order')
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'delivered')
                    ->whereNotNull('delivered_at');
            })
            ->get()
            ->sortByDesc(fn (OrderItem $item) => $this->receivedAt($item)?->timestamp ?? 0)
            ->first();

        $receivedAt = $latestItem ? $this->receivedAt($latestItem) : null;

        if (! $latestItem || ! $receivedAt || $receivedAt->lt(now()->subDays(30))) {
            return null;
        }

        return $latestItem;
    }

    private function receivedAt(?OrderItem $item)
    {
        return $item?->delivered_at ?: $item?->order?->delivered_at;
    }
}
