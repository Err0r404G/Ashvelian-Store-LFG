@extends('layouts.portal')

@section('title', 'Coupon Campaigns | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Coupon Campaigns</h1><p class="fs-5 muted mt-2">Platform-funded discounts validated during checkout.</p></div></div>

    <section class="panel panel-pad mb-4">
        <h2 class="fw-black mb-3">Create Campaign</h2>
        <form method="post" action="{{ route('admin.coupons.store') }}" class="row g-3">
            @csrf
            <div class="col-md-2"><input class="form-control" name="code" placeholder="ELITE24" required></div>
            <div class="col-md-3"><input class="form-control" name="name" placeholder="Campaign name" required></div>
            <div class="col-md-2"><select class="form-select" name="type"><option value="percent">Percent</option><option value="fixed">Fixed</option></select></div>
            <div class="col-md-1"><input class="form-control" name="value" type="number" step="0.01" placeholder="20" required></div>
            <div class="col-md-2"><input class="form-control" name="minimum_order_amount" type="number" step="0.01" placeholder="Minimum"></div>
            <div class="col-md-2 form-check form-switch pt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" name="is_active" value="1" type="checkbox" checked> Active</div>
            <div class="col-12"><button class="btn-ash" type="submit">Create Coupon</button></div>
        </form>
    </section>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Name</th><th>Discount</th><th>Minimum</th><th>Usage</th><th>Status</th><th>Update</th><th>Delete</th></tr></thead>
            <tbody>
                @foreach ($coupons as $coupon)
                    <tr>
                        <td><strong>{{ $coupon->code }}</strong></td>
                        <td>{{ $coupon->name }}</td>
                        <td>{{ $coupon->type === 'percent' ? $coupon->value.'%' : '৳'.number_format($coupon->value, 2) }}</td>
                        <td>৳{{ number_format($coupon->minimum_order_amount, 2) }}</td>
                        <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                        <td><span class="status-pill {{ $coupon->is_active ? 'green' : '' }}">{{ $coupon->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td>
                            <form method="post" action="{{ route('admin.coupons.update', $coupon) }}" class="d-grid gap-2" style="min-width:260px;">
                                @csrf
                                @method('PUT')
                                <input class="form-control form-control-sm" name="code" value="{{ $coupon->code }}" required>
                                <input class="form-control form-control-sm" name="name" value="{{ $coupon->name }}" required>
                                <select class="form-select form-select-sm" name="type"><option value="percent" @selected($coupon->type === 'percent')>Percent</option><option value="fixed" @selected($coupon->type === 'fixed')>Fixed</option></select>
                                <input class="form-control form-control-sm" name="value" type="number" step="0.01" value="{{ $coupon->value }}" required>
                                <input class="form-control form-control-sm" name="minimum_order_amount" type="number" step="0.01" value="{{ $coupon->minimum_order_amount }}">
                                <input type="hidden" name="is_active" value="0">
                                <label class="small"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Active</label>
                                <button class="btn-ghost py-1" type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.coupons.destroy', $coupon) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm text-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $coupons->links() }}</div>
    </section>
@endsection
