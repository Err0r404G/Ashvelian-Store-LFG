<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('manager.reviews.index', [
            'reviews' => Review::with(['user', 'product'])->latest()->paginate(12),
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'manager_reply' => ['nullable', 'string', 'max:2000'],
            'is_featured' => ['nullable', 'boolean'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $review->update($data + ['is_featured' => false, 'is_approved' => false]);

        return back()->with('status', 'Review response updated.');
    }
}
