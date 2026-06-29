<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::whereNull('parent_id');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%')
                  ->orWhereHas('children', function($qc) use ($search) {
                      $qc->where('name', 'like', '%'.$search.'%');
                  });
            });
        }

        $categories = $query->with(['products.orderItems', 'children.products.orderItems'])
            ->orderBy('sort_order')
            ->paginate(5)
            ->withQueryString();

        return view('manager.categories.index', [
            'categories' => $categories,
            'parents' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return back()->with('status', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category->id));

        return back()->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->withErrors(['category' => 'Move products and subcategories before deleting this category.']);
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($ignoreId && (int) ($data['parent_id'] ?? 0) === $ignoreId) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        $base = Str::slug($data['name']);
        $slug = $base;
        $counter = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $data + [
            'slug' => $slug,
            'sort_order' => 0,
            'is_active' => false,
        ];
    }
}
