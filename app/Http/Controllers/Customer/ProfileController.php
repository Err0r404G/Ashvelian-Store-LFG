<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Mail\EmailChangeOtpMail;
use App\Models\PendingEmailChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('customer.profile', [
            'user' => $request->user(),
            'emailChangeAvailableAt' => $this->emailChangeAvailableAt($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function requestEmailChange(Request $request)
    {
        $user = $request->user();

        $this->ensureEmailChangeAllowed($user);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
        ]);

        $newEmail = strtolower($data['email']);

        if ($newEmail === strtolower($user->email)) {
            throw ValidationException::withMessages(['email' => 'Enter a different email address.']);
        }

        PendingEmailChange::where('user_id', $user->id)->delete();

        $otp = (string) random_int(100000, 999999);
        $pendingEmailChange = PendingEmailChange::create([
            'user_id' => $user->id,
            'new_email' => $newEmail,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendEmailChangeOtp($pendingEmailChange);

        $request->session()->put('pending_email_change_id', $pendingEmailChange->id);
        $request->session()->put('demo_email_change_otp', $otp);

        return redirect()
            ->route('customer.profile.email.verify')
            ->with('status', 'We sent a 6-digit OTP to '.$pendingEmailChange->new_email.'.');
    }

    public function showEmailChangeOtp(Request $request)
    {
        $pendingEmailChange = $this->pendingEmailChangeFromSession($request);

        if (! $pendingEmailChange) {
            return redirect()->route('customer.profile.edit')->withErrors(['email' => 'Please request an email change first.']);
        }

        return view('customer.email-verify', [
            'pendingEmailChange' => $pendingEmailChange,
            'demoOtp' => config('app.debug') ? $request->session()->get('demo_email_change_otp') : null,
        ]);
    }

    public function verifyEmailChangeOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingEmailChange = $this->pendingEmailChangeFromSession($request);

        if (! $pendingEmailChange) {
            return redirect()->route('customer.profile.edit')->withErrors(['email' => 'Please request an email change again.']);
        }

        if ($pendingEmailChange->isExpired()) {
            $pendingEmailChange->delete();
            $request->session()->forget(['pending_email_change_id', 'demo_email_change_otp']);

            return redirect()->route('customer.profile.edit')->withErrors(['email' => 'The OTP expired. Please request a new email change.']);
        }

        if ($pendingEmailChange->otp_code !== $data['otp']) {
            $pendingEmailChange->increment('attempts');

            if ($pendingEmailChange->attempts >= 4) {
                $pendingEmailChange->delete();
                $request->session()->forget(['pending_email_change_id', 'demo_email_change_otp']);

                return redirect()->route('customer.profile.edit')->withErrors(['email' => 'Too many incorrect OTP attempts. Please request a new email change.']);
            }

            return back()->withErrors(['otp' => 'The OTP did not match.'])->onlyInput('otp');
        }

        $this->ensureEmailChangeAllowed($request->user());

        if (User::where('email', $pendingEmailChange->new_email)->where('id', '!=', $request->user()->id)->exists()) {
            return redirect()->route('customer.profile.edit')->withErrors(['email' => 'That email address is already in use.']);
        }

        $request->user()->update([
            'email' => $pendingEmailChange->new_email,
            'email_verified_at' => now(),
            'email_changed_at' => now(),
        ]);

        $pendingEmailChange->delete();
        $request->session()->forget(['pending_email_change_id', 'demo_email_change_otp']);

        return redirect()->route('customer.profile.edit')->with('status', 'Email address updated.');
    }

    public function resendEmailChangeOtp(Request $request)
    {
        $pendingEmailChange = $this->pendingEmailChangeFromSession($request);

        if (! $pendingEmailChange) {
            return redirect()->route('customer.profile.edit')->withErrors(['email' => 'Please request an email change again.']);
        }

        $this->ensureEmailChangeAllowed($request->user());

        $otp = (string) random_int(100000, 999999);
        $pendingEmailChange->update([
            'otp_code' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendEmailChangeOtp($pendingEmailChange);
        $request->session()->put('demo_email_change_otp', $otp);

        return back()->with('status', 'A new OTP was sent to '.$pendingEmailChange->new_email.'.');
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

    private function pendingEmailChangeFromSession(Request $request): ?PendingEmailChange
    {
        $id = $request->session()->get('pending_email_change_id');

        if (! $id) {
            return null;
        }

        return PendingEmailChange::where('user_id', $request->user()->id)->find($id);
    }

    private function sendEmailChangeOtp(PendingEmailChange $pendingEmailChange): void
    {
        try {
            Mail::to($pendingEmailChange->new_email)->send(new EmailChangeOtpMail($pendingEmailChange));
        } catch (\Throwable $exception) {
            report($exception);
        }

        logger()->info('Ashvalian email change OTP', [
            'user_id' => $pendingEmailChange->user_id,
            'destination' => $pendingEmailChange->new_email,
            'otp' => $pendingEmailChange->otp_code,
        ]);
    }

    private function ensureEmailChangeAllowed(User $user): void
    {
        $availableAt = $this->emailChangeAvailableAt($user);

        if ($availableAt && $availableAt->isFuture()) {
            throw ValidationException::withMessages([
                'email' => 'You can change your email again on '.$availableAt->format('M j, Y').'.',
            ]);
        }
    }

    private function emailChangeAvailableAt(User $user)
    {
        return $user->email_changed_at?->copy()->addDays(30);
    }
}
