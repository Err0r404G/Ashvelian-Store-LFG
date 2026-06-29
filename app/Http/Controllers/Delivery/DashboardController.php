<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        return view('delivery.dashboard', [
            'pendingDispatch' => Shipment::where('status', 'pending_dispatch')->count(),
            'activeDeliveries' => Shipment::whereIn('status', ['confirmed', 'processing', 'shipped', 'in_transit', 'out_for_delivery'])->count(),
            'deliveredToday' => Shipment::whereDate('delivered_at', today())->count(),
            'failedDeliveries' => Shipment::where('status', 'failed')->count(),
            'incomingOrders' => Order::with(['user', 'shipment', 'items.product'])->latest()->take(10)->get(),
            'urgentShipment' => Shipment::with('order')->whereIn('status', ['pending_dispatch', 'failed'])->latest()->first(),
        ]);
    }

    public function active()
    {
        return view('delivery.active', [
            'shipments' => Shipment::with(['order.user'])->whereIn('status', ['confirmed', 'processing', 'shipped', 'in_transit', 'out_for_delivery', 'failed'])->latest()->paginate(10),
            'inTransit' => Shipment::whereIn('status', ['shipped', 'in_transit', 'out_for_delivery'])->count(),
            'outForDelivery' => Shipment::where('status', 'out_for_delivery')->count(),
            'onTimeRate' => 98.2,
        ]);
    }

    public function incoming(Request $request)
    {
        $status = $request->input('status');

        $items = OrderItem::with(['order.user', 'order.shipment', 'product'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('delivery.incoming', [
            'items' => $items,
            'status' => $status,
            'statusOptions' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'failed', 'returned'],
        ]);
    }

    public function dispatch()
    {
        return view('delivery.dispatch', [
            'shipments' => Shipment::with(['order.user', 'order.items.product'])
                ->where('status', 'pending_dispatch')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function show(Shipment $shipment)
    {
        return view('delivery.show', [
            'shipment' => $shipment->load(['order.user', 'order.items.product', 'events']),
        ]);
    }

    public function returns()
    {
        return view('delivery.returns', [
            'failedShipments' => Shipment::with('order')->whereIn('status', ['failed', 'returned'])->latest()->take(10)->get(),
            'returnRequests' => ReturnRequest::with(['order', 'user'])->latest()->take(8)->get(),
        ]);
    }

    public function history()
    {
        return view('delivery.history', [
            'shipments' => Shipment::with('order')
                ->whereIn('status', ['delivered', 'failed', 'returned'])
                ->latest('updated_at')
                ->paginate(12),
        ]);
    }

    public function summary()
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();

        return view('delivery.summary', [
            'daily' => [
                'delivered' => Shipment::where('status', 'delivered')->where('updated_at', '>=', $today)->count(),
                'failed' => Shipment::where('status', 'failed')->where('updated_at', '>=', $today)->count(),
                'in_transit' => Shipment::whereIn('status', ['shipped', 'in_transit', 'out_for_delivery'])->count(),
            ],
            'weekly' => [
                'delivered' => Shipment::where('status', 'delivered')->where('updated_at', '>=', $week)->count(),
                'failed' => Shipment::where('status', 'failed')->where('updated_at', '>=', $week)->count(),
                'in_transit' => Shipment::whereIn('status', ['shipped', 'in_transit', 'out_for_delivery'])->count(),
            ],
            'shipments' => Shipment::with('order')->latest()->take(8)->get(),
        ]);
    }

    public function updateItem(Request $request, OrderItem $orderItem)
    {
        $data = $request->validate([
            'status' => ['required', 'in:confirmed,processing,shipped,delivered,failed,returned'],
            'tracking_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $orderItem->canTransitionTo($data['status'])) {
            throw ValidationException::withMessages([
                'status' => 'This item can only move to the next delivery status.',
            ]);
        }

        $orderItem->update([
            'status' => $data['status'],
            'tracking_note' => $data['tracking_note'] ?? $orderItem->tracking_note,
            'confirmed_at' => in_array($data['status'], ['confirmed', 'processing', 'shipped', 'delivered'], true) ? ($orderItem->confirmed_at ?: now()) : $orderItem->confirmed_at,
            'shipped_at' => in_array($data['status'], ['shipped', 'delivered'], true) ? ($orderItem->shipped_at ?: now()) : $orderItem->shipped_at,
            'delivered_at' => $data['status'] === 'delivered' ? ($orderItem->delivered_at ?: now()) : $orderItem->delivered_at,
        ]);

        return back()->with('status', 'Order item status updated.');
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending_dispatch,confirmed,processing,shipped,in_transit,out_for_delivery,delivered,failed,returned'],
            'tracking_notes' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        $shipment->update([
            'delivery_manager_id' => $request->user()->id,
            'status' => $data['status'],
            'tracking_notes' => $data['tracking_notes'] ?? $shipment->tracking_notes,
            'shipped_at' => in_array($data['status'], ['shipped', 'in_transit', 'out_for_delivery', 'delivered'], true) ? ($shipment->shipped_at ?: now()) : $shipment->shipped_at,
            'delivered_at' => $data['status'] === 'delivered' ? now() : $shipment->delivered_at,
        ]);

        $shipment->order->update(['status' => match ($data['status']) {
            'pending_dispatch' => 'confirmed',
            'confirmed' => 'confirmed',
            'processing' => 'processing',
            'shipped', 'in_transit' => 'shipped',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'failed' => 'failed',
            'returned' => 'returned',
        }]);

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $data['status'],
            'location' => $data['location'] ?? null,
            'notes' => $data['tracking_notes'] ?? null,
            'occurred_at' => now(),
        ]);

        return back()->with('status', 'Shipment updated.');
    }
}
