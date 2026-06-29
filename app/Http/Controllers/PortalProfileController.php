<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PortalProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('portal.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($user->isAdmin()) {
            $rules['email'] = ['required', 'email', 'max:190', 'unique:users,email,'.$user->id];
        }

        $data = $request->validate($rules);

        $user->update($data);

        return back()->with('status', 'Profile details updated.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password changed.');
    }
}
