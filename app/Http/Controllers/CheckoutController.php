<?php

namespace App\Http\Controllers;

use App\Mail\OrderInvoiceMail;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(Request $request, CartController $cartController)
    {
        $payload = $cartController->payload($request, false);

        if ($payload['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Add items before checkout.');
        }

        return view('store.checkout', $payload + [
            'zones' => DeliveryZone::where('is_active', true)->get(),
            'addresses' => $request->user()->addresses()->with('deliveryZone')->get(),
        ]);
    }

    public function store(Request $request, CartController $cartController)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'address_id' => ['nullable', 'exists:customer_addresses,id'],
            'first_name' => ['required_without:address_id', 'nullable', 'string', 'max:80'],
            'last_name' => ['required_without:address_id', 'nullable', 'string', 'max:80'],
            'street_address' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'delivery_zone_id' => ['required_without:address_id', 'nullable', 'exists:delivery_zones,id'],
            'phone' => ['required_without:address_id', 'nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cod,bkash,sslcommerz'],
        ]);

        $payload = $cartController->payload($request, false);

        if ($payload['items']->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $zone = DeliveryZone::find($data['delivery_zone_id'] ?? null);
        $address = ($data['address_id'] ?? null)
            ? $request->user()->addresses()->where('id', $data['address_id'])->first()
            : null;

        if ($address) {
            $data['first_name'] = $address->first_name;
            $data['last_name'] = $address->last_name;
            $data['street_address'] = $address->street_address.', '.$address->city;
            $data['phone'] = $address->phone;
            $zone = $address->deliveryZone ?: $zone;
        }

        abort_unless($zone, 422);

        $shipping = (float) $zone->fee;
        $grandTotal = $payload['grandTotal'] + $shipping;

        $order = DB::transaction(function () use ($request, $data, $payload, $zone, $shipping, $grandTotal, $cartController) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'delivery_zone_id' => $zone->id,
                'order_number' => '#ASH-'.now()->format('ymd').'-'.strtoupper(Str::random(4)),
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
                'coupon_code' => $payload['couponCode'],
                'subtotal' => $payload['subtotal'],
                'discount_total' => $payload['discount'],
                'shipping_total' => $shipping,
                'tax_total' => $payload['tax'],
                'grand_total' => $grandTotal,
                'customer_name' => $data['first_name'].' '.$data['last_name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'],
                'shipping_address' => $data['street_address'].', '.$zone->name,
                'placed_at' => now(),
            ]);

            foreach ($payload['items'] as $row) {
                $product = $row['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $row['quantity'],
                    'line_total' => $row['line_total'],
                    'options' => $row['options'],
                ]);

                $options = $row['options'] ?? [];
                $size = $options['size'] ?? null;
                $color = $options['color'] ?? null;

                if ($product->variant_stock && $size && $color) {
                    $variantKey = "{$color} - {$size}";
                    $variantStock = $product->variant_stock;
                    if (isset($variantStock[$variantKey])) {
                        $val = $variantStock[$variantKey];
                        if (is_array($val)) {
                            $val['qty'] = max(0, ($val['qty'] ?? 0) - $row['quantity']);
                            $variantStock[$variantKey] = $val;
                        } else {
                            $variantStock[$variantKey] = max(0, ((int) $val) - $row['quantity']);
                        }
                        $product->variant_stock = $variantStock;
                    }
                }

                $product->stock_quantity = max(0, $product->stock_quantity - $row['quantity']);
                if ($product->stock_quantity === 0) {
                    $product->status = 'out_of_stock';
                }
                $product->save();
            }

            Shipment::create([
                'order_id' => $order->id,
                'tracking_number' => 'TRK-'.strtoupper(Str::random(8)),
                'carrier' => 'Ashvalian Fleet',
                'status' => 'pending_dispatch',
                'estimated_delivery_at' => now()->addDays($zone->estimated_days),
            ]);

            $cartController->clear($request);

            return $order;
        });

        try {
            Mail::to($order->customer_email)->send(new OrderInvoiceMail($order->load(['items', 'shipment'])));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('orders.confirmation', $order)->with('status', 'Order placed successfully. Your electronic invoice has been sent.');
    }
}
