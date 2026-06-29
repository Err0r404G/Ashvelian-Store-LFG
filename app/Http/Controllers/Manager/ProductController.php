<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        return view('manager.products.index', [
            'products' => Product::with('category')->latest()->paginate(10),
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'pendingReturns' => ReturnRequest::where('status', 'pending')->count(),
            'averageRating' => Product::avg('rating_average') ?: 0,
            'featuredProducts' => Product::where('is_featured', true)->take(3)->get(),
            'reviews' => Review::with(['user', 'product'])->latest()->take(4)->get(),
            'categories' => Category::whereNull('parent_id')->withCount('products')->get(),
        ]);
    }

    public function create()
    {
        $categories = Category::with('parent')
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($category) {
                $parentName = $category->parent ? $category->parent->name : $category->name;
                $childName = $category->parent ? $category->name : '';
                return strtolower($parentName . ' _ ' . $childName);
            });

        return view('manager.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->normalizeAvailability($this->validatedData($request));
        $data['created_by'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['primary_image_url'] = $this->primaryImagePath($request, $data['primary_image_url'] ?? null);
        $data['images'] = $this->additionalImages($request, $data['additional_image_urls'] ?? []);
        $data['has_size_guide'] = $request->boolean('has_size_guide');
        $data['size_guide_image'] = $this->sizeGuideImagePath($request, $data['size_guide_image'] ?? null);
        unset($data['primary_image'], $data['additional_images'], $data['additional_image_urls'], $data['size_guide_image_file']);

        Product::create($data);

        return redirect()->route('manager.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::with('parent')
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($category) {
                $parentName = $category->parent ? $category->parent->name : $category->name;
                $childName = $category->parent ? $category->name : '';
                return strtolower($parentName . ' _ ' . $childName);
            });

        return view('manager.products.create', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->normalizeAvailability($this->validatedData($request, $product->id));
        $data['slug'] = $product->name === $data['name'] ? $product->slug : $this->uniqueSlug($data['name'], $product->id);
        $data['primary_image_url'] = $this->primaryImagePath($request, $data['primary_image_url'] ?? $product->primary_image_url);
        $data['images'] = $this->additionalImages($request, $data['additional_image_urls'] ?? ($product->images ?? []));
        $data['has_size_guide'] = $request->boolean('has_size_guide');
        $data['size_guide_image'] = $this->sizeGuideImagePath($request, $data['size_guide_image'] ?? $product->size_guide_image);
        unset($data['primary_image'], $data['additional_images'], $data['additional_image_urls'], $data['size_guide_image_file']);

        $product->update($data);

        return redirect()->route('manager.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $hasPendingOrder = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery']))
            ->exists();

        if ($hasPendingOrder) {
            return back()->withErrors(['product' => 'This product is linked to pending orders and cannot be deleted.']);
        }

        $product->delete();

        return back()->with('status', 'Product hidden and moved to recovery.');
    }

    public function toggleAvailability(Product $product)
    {
        $product->update([
            'status' => $product->status === 'active' ? 'hidden' : 'active',
        ]);

        return back()->with('status', 'Product availability updated.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        if ($request->has('sizes') && is_string($request->input('sizes'))) {
            $sizes = array_values(array_filter(array_map('trim', explode(',', $request->input('sizes')))));
            $request->merge(['sizes' => $sizes]);
        }
        if ($request->has('colors') && is_string($request->input('colors'))) {
            $colors = array_values(array_filter(array_map('trim', explode(',', $request->input('colors')))));
            $request->merge(['colors' => $colors]);
        }
        if ($request->has('variant_stock') && is_array($request->input('variant_stock'))) {
            $variantStock = $request->input('variant_stock');
            $totalStock = 0;
            foreach ($variantStock as $comb => $val) {
                if (is_array($val)) {
                    $variantStock[$comb]['qty'] = max(0, (int) ($val['qty'] ?? 0));
                    $variantStock[$comb]['image'] = $val['image'] ?? null;
                    $totalStock += $variantStock[$comb]['qty'];
                } else {
                    $qty = max(0, (int) $val);
                    $variantStock[$comb] = ['qty' => $qty, 'image' => null];
                    $totalStock += $qty;
                }
            }
            $request->merge([
                'variant_stock' => $variantStock,
                'stock_quantity' => $totalStock
            ]);
        }

        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku,'.($ignoreId ?: 'NULL')],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'image', 'max:5120'],
            'primary_image_url' => ['nullable', 'url'],
            'additional_images.*' => ['nullable', 'image', 'max:5120'],
            'additional_image_urls' => ['nullable', 'array'],
            'additional_image_urls.*' => ['nullable', 'url'],
            'sizes' => ['nullable', 'array'],
            'colors' => ['nullable', 'array'],
            'variant_stock' => ['nullable', 'array'],
            'specifications' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'is_featured' => ['nullable', 'boolean'],
            'is_on_sale' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,draft,hidden,out_of_stock'],
            'has_size_guide' => ['nullable', 'boolean'],
            'size_guide_image' => ['nullable', 'url'],
            'size_guide_image_file' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function primaryImagePath(Request $request, ?string $fallback): ?string
    {
        if (! $request->hasFile('primary_image')) {
            return $fallback;
        }

        return Storage::url($request->file('primary_image')->store('products', 'public'));
    }

    private function normalizeAvailability(array $data): array
    {
        if ((int) $data['stock_quantity'] === 0) {
            $data['status'] = 'out_of_stock';
        } elseif ($data['status'] === 'out_of_stock') {
            $data['status'] = 'active';
        }

        return $data;
    }

    private function additionalImages(Request $request, array $urls): array
    {
        foreach ($request->file('additional_images', []) as $image) {
            $urls[] = Storage::url($image->store('products', 'public'));
        }

        return array_values(array_filter($urls));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function sizeGuideImagePath(Request $request, ?string $fallback): ?string
    {
        if (! $request->hasFile('size_guide_image_file')) {
            return $fallback;
        }

        return Storage::url($request->file('size_guide_image_file')->store('size_guides', 'public'));
    }
}
