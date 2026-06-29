@extends('layouts.portal')

@section('title', 'Announcements | Ashvalian')

@section('content')
    <div class="portal-header"><div class="portal-title"><h1>Announcements</h1><p class="fs-5 muted mt-2">Post platform-wide messages to all users.</p></div></div>

    <section class="panel panel-pad mb-4">
        <form method="post" action="{{ route('admin.announcements.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><input class="form-control" name="title" placeholder="Announcement title" required></div>
            <div class="col-md-6"><input class="form-control" name="message" placeholder="Message" required></div>
            <div class="col-md-2 form-check form-switch pt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> Active</div>
            <div class="col-12"><button class="btn-ash" type="submit">Post Announcement</button></div>
        </form>
    </section>

    <section class="panel">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Message</th><th>Posted By</th><th>Status</th><th>Update</th><th>Delete</th></tr></thead>
            <tbody>
                @foreach ($announcements as $announcement)
                    <tr>
                        <td><strong>{{ $announcement->title }}</strong></td>
                        <td>{{ $announcement->message }}</td>
                        <td>{{ $announcement->user?->name }}</td>
                        <td><span class="status-pill {{ $announcement->is_active ? 'green' : '' }}">{{ $announcement->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td>
                            <form method="post" action="{{ route('admin.announcements.update', $announcement) }}" class="d-grid gap-2" style="min-width:300px;">
                                @csrf
                                @method('PUT')
                                <input class="form-control form-control-sm" name="title" value="{{ $announcement->title }}" required>
                                <textarea class="form-control form-control-sm" name="message" rows="2" required>{{ $announcement->message }}</textarea>
                                <input type="hidden" name="is_active" value="0">
                                <label class="small"><input type="checkbox" name="is_active" value="1" @checked($announcement->is_active)> Active</label>
                                <button class="btn-ghost py-1" type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.announcements.destroy', $announcement) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm text-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-pad">{{ $announcements->links() }}</div>
    </section>
@endsection
