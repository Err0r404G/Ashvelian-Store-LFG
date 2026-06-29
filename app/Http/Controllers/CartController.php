<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return view('store.cart', $this->payload($request));
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, min(20, (int) $request->input('quantity', 1)));
        $options = array_filter($request->only(['size', 'color']));

        if ($request->user()) {
            $cart = $this->cartFor($request);
            $item = $cart->items()->where('product_id', $product->id)->get()->first(function($i) use ($options) {
                return $i->options == $options;
            });
            if (!$item) {
                $item = new \App\Models\CartItem(['product_id' => $product->id]);
                $item->cart_id = $cart->id;
                $item->quantity = 0;
            }
            $item->quantity = min(20, $item->quantity + $quantity);
            $item->options = $options;
            $item->save();
        } else {
            $items = $request->session()->get('cart.items', []);
            $key = $product->id . '_' . md5(json_encode($options));
            if (isset($items[$key])) {
                $items[$key]['quantity'] = min(20, $items[$key]['quantity'] + $quantity);
            } else {
                $items[$key] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'options' => $options,
                ];
            }
            $request->session()->put('cart.items', $items);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Added to bag', 'cart_count' => $this->count($request)]);
        }

        return back()->with('status', 'Added to bag.');
    }

    public function update(Request $request, Product $product)
    {
        $quantity = max(0, min(20, (int) $request->input('quantity', 1)));
        $options = array_filter($request->input('options', []));

        if ($request->user()) {
            $cart = $this->cartFor($request);
            $item = $cart->items()->where('product_id', $product->id)->get()->first(function($i) use ($options) {
                return $i->options == $options;
            });
            if ($item) {
                $quantity === 0 ? $item->delete() : $item->update(['quantity' => $quantity]);
            }
        } else {
            $items = $request->session()->get('cart.items', []);
            $key = $product->id . '_' . md5(json_encode($options));
            if (isset($items[$key])) {
                if ($quantity === 0) {
                    unset($items[$key]);
                } else {
                    $items[$key]['quantity'] = $quantity;
                }
                $request->session()->put('cart.items', $items);
            }
        }

        return back()->with('status', 'Bag updated.');
    }

    public function remove(Request $request, Product $product)
    {
        return $this->update($request->merge(['quantity' => 0]), $product);
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $payload = $this->payload($request, false);
        $coupon = Coupon::where('code', strtoupper($data['code']))->first();

        if (! $coupon || ! $coupon->isValidFor($payload['subtotal'])) {
            $message = 'This coupon is not valid for the current bag.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['coupon' => $message]);
        }

        if ($request->user()) {
            $this->cartFor($request)->update(['coupon_code' => $coupon->code]);
        } else {
            $request->session()->put('cart.coupon_code', $coupon->code);
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'Coupon applied.', 'discount' => $coupon->discountFor($payload['subtotal'])])
            : back()->with('status', 'Coupon applied.');
    }

    public function clear(Request $request): void
    {
        if ($request->user()) {
            $this->cartFor($request)->items()->delete();
        }

        $request->session()->forget(['cart.items', 'cart.coupon_code']);
    }

    public function payload(Request $request, bool $withRecommendations = true): array
    {
        $rows = collect();
        $couponCode = null;

        if ($request->user()) {
            $cart = $this->cartFor($request)->load('items.product.category');
            $couponCode = $cart->coupon_code;
            $rows = $cart->items->filter->product->map(fn ($item) => [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'options' => $item->options ?? [],
                'line_total' => (float) $item->product->price * $item->quantity,
            ]);
        } else {
            $items = $request->session()->get('cart.items', []);
            $couponCode = $request->session()->get('cart.coupon_code');
            $productIds = collect($items)->pluck('product_id')->unique()->toArray();
            $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');
            $rows = collect($items)->map(function ($item, $key) use ($products) {
                $product = $products->get($item['product_id']);
                if (!$product) return null;
                return [
                    'product' => $product,
                    'quantity' => max(1, (int) $item['quantity']),
                    'options' => $item['options'] ?? [],
                    'line_total' => (float) $product->price * max(1, (int) $item['quantity']),
                ];
            })->filter();
        }

        $subtotal = $rows->sum('line_total');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;
        $discount = $coupon?->discountFor($subtotal) ?? 0;
        $tax = round(max(0, $subtotal - $discount) * 0.08, 2);
        $grandTotal = max(0, $subtotal - $discount + $tax);

        return [
            'items' => $rows,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'couponCode' => $coupon?->code,
            'tax' => $tax,
            'grandTotal' => $grandTotal,
            'recommendations' => $withRecommendations
                ? Product::where('status', 'active')->where('is_featured', true)->take(3)->get()
                : collect(),
        ];
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id], ['session_id' => $request->session()->getId()]);
    }

    private function count(Request $request): int
    {
        return $this->payload($request, false)['items']->sum('quantity');
    }
}
