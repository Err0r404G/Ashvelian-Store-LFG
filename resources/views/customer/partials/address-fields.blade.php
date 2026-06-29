<div class="col-md-3">
    <select class="form-select" name="label" required>
        @foreach (['Home', 'Work', 'Office', 'Other'] as $opt)
            <option value="{{ $opt }}" @selected(old('label', $address->label ?? 'Home') == $opt)>{{ $opt }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3"><input class="form-control" name="first_name" value="{{ old('first_name', $address->first_name ?? '') }}" placeholder="First name" required></div>
<div class="col-md-3"><input class="form-control" name="last_name" value="{{ old('last_name', $address->last_name ?? '') }}" placeholder="Last name" required></div>
<div class="col-md-3"><input class="form-control" name="phone" value="{{ old('phone', $address->phone ?? '') }}" placeholder="Phone" required></div>
<div class="col-md-5"><input class="form-control" name="street_address" value="{{ old('street_address', $address->street_address ?? '') }}" placeholder="Street address" required></div>
<div class="col-md-3"><input class="form-control" name="city" value="{{ old('city', $address->city ?? '') }}" placeholder="City" required></div>
<div class="col-md-2"><input class="form-control" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" placeholder="Postal"></div>
<div class="col-md-2">
    <select class="form-select" name="country" required>
        <option value="">--Please Choose--</option>
        <option value="Bangladesh" @selected(old('country', $address->country ?? 'Bangladesh') == 'Bangladesh')>Bangladesh</option>
    </select>
</div>
<div class="col-md-4">
    <select class="form-select" name="delivery_zone_id">
        <option value="">Choose delivery zone</option>
        @foreach ($zones as $zone)
            <option value="{{ $zone->id }}" @selected(old('delivery_zone_id', $address->delivery_zone_id ?? '') == $zone->id)>{{ $zone->name }} - ৳{{ number_format($zone->fee, 2) }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 form-check form-switch pt-2">
    <input type="hidden" name="is_default" value="0">
    <input class="form-check-input" type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default ?? false))> Default
</div>
