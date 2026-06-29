@extends('layouts.portal')

@section('title', 'Delivery Zones | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Delivery Zones</h1><p class="fs-5 muted mt-2">Set delivery fees and estimated delivery days per zone.</p></div></div>

    <section class="panel panel-pad mb-4">
        <form method="post" action="{{ route('manager.delivery-zones.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><input class="form-control" name="name" placeholder="Dhaka City" required></div>
            <div class="col-md-2"><input class="form-control" name="fee" type="number" step="0.01" placeholder="Fee" required></div>
            <div class="col-md-2"><input class="form-control" name="estimated_days" type="number" placeholder="Days" required></div>
            <div class="col-md-2 form-check form-switch pt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> Active</div>
            <div class="col-md-2"><button class="btn-ash w-100" type="submit">Add Zone</button></div>
        </form>
    </section>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Zone</th><th>Fee</th><th>Estimated Days</th><th>Status</th><th>Update</th><th>Delete</th></tr></thead>
            <tbody>
                @foreach ($zones as $zone)
                    <tr>
                        <td><strong>{{ $zone->name }}</strong></td>
                        <td>৳{{ number_format($zone->fee, 2) }}</td>
                        <td>{{ $zone->estimated_days }} days</td>
                        <td><span class="status-pill {{ $zone->is_active ? 'green' : '' }}">{{ $zone->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td>
                            <form method="post" action="{{ route('manager.delivery-zones.update', $zone) }}" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <input class="form-control form-control-sm" name="name" value="{{ $zone->name }}" required>
                                <input class="form-control form-control-sm" name="fee" type="number" step="0.01" value="{{ $zone->fee }}" required>
                                <input class="form-control form-control-sm" name="estimated_days" type="number" value="{{ $zone->estimated_days }}" required>
                                <input type="hidden" name="is_active" value="0">
                                <label class="small"><input type="checkbox" name="is_active" value="1" @checked($zone->is_active)> Active</label>
                                <button class="btn-ghost py-1" type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('manager.delivery-zones.destroy', $zone) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm text-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $zones->links() }}</div>
    </section>
@endsection
