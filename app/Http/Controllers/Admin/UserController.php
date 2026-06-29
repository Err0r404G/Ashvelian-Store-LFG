<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('email', 'like', '%'.$request->q.'%')
                        ->orWhere('phone', 'like', '%'.$request->q.'%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roleCounts' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function toggleRestriction(User $user)
    {
        abort_unless($user->role === 'customer', 422, 'Only customer accounts can be restricted.');

        $user->update(['is_restricted' => ! $user->is_restricted]);

        return back()->with('status', $user->is_restricted ? 'Customer restricted.' : 'Customer unrestricted.');
    }
}
