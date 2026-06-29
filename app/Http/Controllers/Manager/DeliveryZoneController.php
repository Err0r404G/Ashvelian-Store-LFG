<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        return view('manager.delivery-zones.index', [
            'zones' => DeliveryZone::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        DeliveryZone::create($this->validated($request));

        return back()->with('status', 'Delivery zone created.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $deliveryZone->update($this->validated($request));

        return back()->with('status', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();

        return back()->with('status', 'Delivery zone deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'fee' => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
