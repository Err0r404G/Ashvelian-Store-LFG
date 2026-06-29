<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $this->authorizeCustomer($request, $order);

        return view('customer.order', ['order' => $order->load(['items.product', 'shipment.events'])]);
    }

    public function confirmation(Request $request, Order $order)
    {
        $this->authorizeCustomer($request, $order);

        return view('customer.confirmation', ['order' => $order->load(['items.product', 'shipment', 'deliveryZone'])]);
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeCustomer($request, $order);

        if (! in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
            return back()->withErrors(['order' => 'This order can no longer be cancelled.']);
        }

        $order->update(['status' => 'cancelled']);
        $order->shipment?->update(['status' => 'returned']);

        return back()->with('status', 'Order cancelled.');
    }

    public function invoice(Request $request, Order $order)
    {
        $this->authorizeCustomer($request, $order);

        return view('customer.invoice', ['order' => $order->load('items')]);
    }

    private function authorizeCustomer(Request $request, Order $order): void
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 403);
    }
}
