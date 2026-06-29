<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        return view('store.wishlist', [
            'items' => Wishlist::with('product.category')->where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function toggle(Request $request, Product $product)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Removed from wishlist.';
        } else {
            Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $product->id]);
            $message = 'Saved to wishlist.';
        }

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('status', $message);
    }

    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();

        return back()->with('status', 'Removed from wishlist.');
    }
}
