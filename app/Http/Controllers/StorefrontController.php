<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        return view('store.home', [
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->get(),
            'announcements' => Announcement::where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->latest()
                ->get(),
            'categories' => Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get(),
            'featuredProducts' => Product::with('category')->where('status', 'active')->where('is_featured', true)->orderBy('featured_sort_order')->take(8)->get(),
            'saleProducts' => Product::with('category')->where('status', 'active')->where('is_on_sale', true)->orderBy('sale_sort_order')->take(8)->get(),
            'reviews' => Review::with(['user', 'product'])->where('is_featured', true)->where('is_approved', true)->latest()->take(3)->get(),
        ]);
    }

    public function shop(Request $request)
    {
        $query = Product::with('category')->where('status', 'active');

        $this->applyProductFilters($query, $request);
        $this->applyProductSorting($query, $request->input('sort', 'featured'));

        return view('store.shop', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $categoryIds = $category->children()->pluck('id')->push($category->id);
        $query = Product::with('category')->whereIn('category_id', $categoryIds)->where('status', 'active');

        $this->applyProductFilters($query, $request);
        $this->applyProductSorting($query, $request->input('sort', 'featured'));

        return view('store.category', [
            'category' => $category,
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::where('parent_id', $category->id)->where('is_active', true)->get(),
        ]);
    }

    public function product(string $slug)
    {
        $product = Product::with(['category', 'creator', 'reviews.user'])->where('slug', $slug)->where('status', 'active')->firstOrFail();

        $canReview = false;
        $existingReview = null;
        if (auth()->check()) {
            $user = auth()->user();
            $existingReview = Review::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            $hasDeliveredOrder = \App\Models\OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status', 'delivered')
                        ->whereNotNull('delivered_at');
                })
                ->exists();

            $reviewableDelivery = null;
            if ($hasDeliveredOrder) {
                $latestItem = \App\Models\OrderItem::with('order')
                    ->where('product_id', $product->id)
                    ->whereHas('order', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where('status', 'delivered')
                            ->whereNotNull('delivered_at');
                    })
                    ->get()
                    ->sortByDesc(function ($item) {
                        $deliveredAt = $item->delivered_at ?: $item->order->delivered_at;
                        return $deliveredAt ? $deliveredAt->timestamp : 0;
                    })
                    ->first();
                
                $deliveredAt = $latestItem ? ($latestItem->delivered_at ?: $latestItem->order->delivered_at) : null;
                if ($latestItem && $deliveredAt && $deliveredAt->gt(now()->subDays(30))) {
                    $reviewableDelivery = $latestItem;
                }
            }

            if ($existingReview || $reviewableDelivery) {
                $canReview = true;
            }
        }

        return view('store.product', [
            'product' => $product,
            'canReview' => $canReview,
            'existingReview' => $existingReview,
            'relatedProducts' => Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'active')
                ->take(4)
                ->get(),
        ]);
    }

    private function applyProductFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $query->where(function ($inner) use ($request) {
                $inner->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%')
                    ->orWhere('sku', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('category_id')) {
            $category = Category::find($request->integer('category_id'));

            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->price_max);
        }

        if ($request->filled('rating')) {
            $query->where('rating_average', '>=', (float) $request->rating);
        }
    }

    private function applyProductSorting($query, string $sort): void
    {
        match ($sort) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating_average'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };
    }

    public function sizeGuide(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        abort_unless($product->has_size_guide && $product->size_guide_image, 404);
        return view('store.size-guide', ['product' => $product]);
    }
}
