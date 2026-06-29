<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    public function index()
    {
        return view('manager.homepage.index', [
            'products' => Product::with('category')->orderBy('name')->get(),
            'featuredProducts' => Product::with('category')
                ->where('is_featured', true)
                ->orderBy('featured_sort_order')
                ->orderBy('name')
                ->get(),
            'saleProducts' => Product::with('category')
                ->where('is_on_sale', true)
                ->orderBy('sale_sort_order')
                ->orderBy('name')
                ->get(),
            'banners' => Banner::orderBy('sort_order')->get(),
            'announcements' => Announcement::latest()->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'featured_product_ids' => ['nullable', 'array', 'max:8'],
            'featured_product_ids.*' => ['integer', 'exists:products,id'],
            'sale_product_ids' => ['nullable', 'array', 'max:8'],
            'sale_product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        DB::transaction(function () use ($data) {
            Product::query()->update([
                'is_featured' => false,
                'featured_sort_order' => 0,
                'is_on_sale' => false,
                'sale_sort_order' => 0,
            ]);

            foreach (array_values($data['featured_product_ids'] ?? []) as $index => $productId) {
                Product::whereKey($productId)->update([
                    'is_featured' => true,
                    'featured_sort_order' => $index + 1,
                ]);
            }

            foreach (array_values($data['sale_product_ids'] ?? []) as $index => $productId) {
                Product::whereKey($productId)->update([
                    'is_on_sale' => true,
                    'sale_sort_order' => $index + 1,
                ]);
            }
        });

        return back()->with('status', 'Homepage product sections updated.');
    }

    // Manager Banner CRUD Handlers
    public function storeBanner(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_file')) {
            $data['image_url'] = Storage::url($request->file('image_file')->store('banners', 'public'));
        } elseif (empty($data['image_url'])) {
            return back()->withErrors(['image_url' => 'Either an image file or an image URL is required.']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Banner::create($data);

        return back()->with('status', 'Banner slide added successfully.');
    }

    public function updateBanner(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_file')) {
            $data['image_url'] = Storage::url($request->file('image_file')->store('banners', 'public'));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $banner->update($data);

        return back()->with('status', 'Banner slide updated successfully.');
    }

    public function destroyBanner(Banner $banner)
    {
        $banner->delete();
        return back()->with('status', 'Banner slide deleted.');
    }

    // Manager Announcement CRUD Handlers
    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['user_id'] = $request->user()->id;

        Announcement::create($data);

        return back()->with('status', 'Announcement added successfully.');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $announcement->update($data);

        return back()->with('status', 'Announcement updated successfully.');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('status', 'Announcement deleted.');
    }
}
