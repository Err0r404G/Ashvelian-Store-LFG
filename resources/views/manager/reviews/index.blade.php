@extends('layouts.portal')

@section('title', 'Product Reviews | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Product Reviews</h1><p class="fs-5 muted mt-2">View and reply to customer reviews.</p></div></div>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Product</th><th>Customer</th><th>Review</th><th>Reply / Moderate</th></tr></thead>
            <tbody>
                @foreach ($reviews as $review)
                    <tr>
                        <td><strong>{{ $review->product?->name }}</strong><div class="text-primary">★★★★★ {{ $review->rating }}</div></td>
                        <td>{{ $review->user?->name }}</td>
                        <td><strong>{{ $review->title }}</strong><p class="mb-0 muted">{{ $review->body }}</p></td>
                        <td>
                            <form method="post" action="{{ route('manager.reviews.update', $review) }}" class="d-grid gap-2" style="min-width:320px;">
                                @csrf
                                @method('PATCH')
                                <textarea class="form-control form-control-sm" name="manager_reply" rows="3" placeholder="Reply to customer">{{ $review->manager_reply }}</textarea>
                                <input type="hidden" name="is_featured" value="0">
                                <input type="hidden" name="is_approved" value="0">
                                <label><input type="checkbox" name="is_featured" value="1" @checked($review->is_featured)> Featured</label>
                                <label><input type="checkbox" name="is_approved" value="1" @checked($review->is_approved)> Approved</label>
                                <button class="btn-ghost py-1" type="submit">Save Reply</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $reviews->links() }}</div>
    </section>
@endsection
