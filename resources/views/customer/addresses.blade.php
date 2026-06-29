@extends('layouts.storefront')

@section('title', 'Shipping Addresses | Ashvalian')

@section('content')
    <section class="page-shell section">
        <h1 class="display-5 fw-black">Shipping Addresses</h1>
        <p class="fs-5 muted mb-5">Add, edit, delete, and set your default delivery address.</p>

        <section class="panel panel-pad mb-5">
            <h2 class="fw-black mb-3">Add Address</h2>
            <form method="post" action="{{ route('customer.addresses.store') }}" class="row g-3">
                @csrf
                @include('customer.partials.address-fields', ['address' => null])
                <div class="col-12"><button class="btn-ash" type="submit">Save Address</button></div>
            </form>
        </section>

        <div class="row g-4">
            @foreach ($addresses as $address)
                <div class="col-lg-6">
                    <article class="panel panel-pad h-100">
                        <div class="d-flex justify-content-between">
                            <h3 class="fw-black">{{ $address->label }}</h3>
                            @if ($address->is_default)<span class="status-pill green">Default</span>@endif
                        </div>
                        <p class="muted">{{ $address->first_name }} {{ $address->last_name }} • {{ $address->phone }}<br>{{ $address->street_address }}, {{ $address->city }} {{ $address->postal_code }}<br>{{ $address->deliveryZone?->name }}</p>
                        <form method="post" action="{{ route('customer.addresses.update', $address) }}" class="row g-2">
                            @csrf
                            @method('PATCH')
                            @include('customer.partials.address-fields', ['address' => $address])
                            <div class="col-md-8"><button class="btn-ghost w-100" type="submit">Update Address</button></div>
                        </form>
                        <form method="post" action="{{ route('customer.addresses.destroy', $address) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm text-danger" type="submit"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endsection
