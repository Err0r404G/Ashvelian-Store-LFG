@extends('layouts.portal')

@section('title', 'Return Requests | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Return Requests</h1><p class="fs-5 muted mt-2">Approve or reject customer return requests with reasons.</p></div></div>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th>Reason</th><th>Status</th><th>Decision</th></tr></thead>
            <tbody>
                @foreach ($returns as $return)
                    <tr>
                        <td><strong>{{ $return->order->order_number }}</strong></td>
                        <td>{{ $return->user->name }}</td>
                        <td>{{ $return->product?->name }}</td>
                        <td>{{ $return->reason }}<div class="muted small">{{ $return->details }}</div></td>
                        <td><span class="status-pill {{ $return->status === 'pending' ? 'red' : 'blue' }}">{{ str_replace('_', ' ', $return->status) }}</span></td>
                        <td>
                            <form method="post" action="{{ route('manager.returns.update', $return) }}" class="d-grid gap-2" style="min-width:260px;">
                                @csrf
                                @method('PATCH')
                                <select class="form-select form-select-sm" name="status">
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                    <option value="return_initiated">Return Initiated</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                                <textarea class="form-control form-control-sm" name="manager_reason" rows="2" placeholder="Reason required" required>{{ $return->manager_reason }}</textarea>
                                <button class="btn-ash py-1" type="submit">Submit Decision</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $returns->links() }}</div>
    </section>
@endsection
