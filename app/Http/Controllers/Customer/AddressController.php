<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return view('customer.addresses', [
            'addresses' => $request->user()->addresses()->with('deliveryZone')->latest()->get(),
            'zones' => DeliveryZone::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($data);

        return back()->with('status', 'Shipping address saved.');
    }

    public function update(Request $request, CustomerAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $this->validated($request);

        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('status', 'Shipping address updated.');
    }

    public function destroy(Request $request, CustomerAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return back()->with('status', 'Shipping address deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'delivery_zone_id' => ['nullable', 'exists:delivery_zones,id'],
            'label' => ['required', 'string', 'max:60'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30'],
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'is_default' => ['nullable', 'boolean'],
        ]) + ['is_default' => false];
    }
}
