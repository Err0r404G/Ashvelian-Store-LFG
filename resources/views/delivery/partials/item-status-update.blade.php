@php
    $nextStatuses = $item->allowedNextStatuses();
    $showCurrent = $showCurrent ?? true;
@endphp

<div class="d-grid gap-2" style="min-width:220px;">
    @if ($showCurrent)
        <span class="status-pill {{ in_array($item->status, ['delivered']) ? 'green' : (in_array($item->status, ['failed', 'returned']) ? 'red' : 'blue') }}">
            {{ str_replace('_', ' ', $item->status) }}
        </span>
    @endif

    @if ($nextStatuses)
        <form method="post" action="{{ route('delivery.order-items.update', $item) }}" class="d-grid gap-2">
            @csrf
            @method('PATCH')
            <select class="form-select form-select-sm" name="status" required>
                <option value="">Next status</option>
                @foreach ($nextStatuses as $option)
                    <option value="{{ $option }}">{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                @endforeach
            </select>
            <input class="form-control form-control-sm" name="tracking_note" value="{{ $item->tracking_note }}" placeholder="Tracking note">
            <button class="btn-ghost py-1" type="submit">Update Status</button>
        </form>
    @else
        <span class="small muted">Final state</span>
    @endif
</div>
