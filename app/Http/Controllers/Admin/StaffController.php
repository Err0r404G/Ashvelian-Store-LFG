<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function create()
    {
        return view('admin.staff.create', [
            'staffRoles' => [
                'admin' => 'Admin',
                'manager' => 'Manager',
                'delivery_manager' => 'Delivery Manager',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:admin,manager,delivery_manager'],
            'password' => ['required', Password::min(8)],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Staff account created.');
    }
}
