<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationOtpMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\Cart;
use App\Models\PendingPasswordReset;
use App\Models\PendingRegistration;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectPathFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => strtolower($data['email']),
            'password' => $data['password'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $request->user()->update(['last_login_at' => now()]);
        $this->mergeSessionCart($request);

        return redirect()->intended($this->redirectPathFor($request->user()));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = strtolower($data['email']);

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'An account already exists with this email address.']);
        }

        PendingRegistration::where('email', $email)->delete();

        $otp = (string) random_int(100000, 999999);
        $pendingRegistration = PendingRegistration::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password']),
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendRegistrationOtp($pendingRegistration);

        $request->session()->put('pending_registration_id', $pendingRegistration->id);
        $request->session()->put('demo_registration_otp', $otp);

        return redirect()
            ->route('register.verify')
            ->with('status', 'We sent a 6-digit OTP to '.$pendingRegistration->destination.'.');
    }

    public function showRegistrationOtp(Request $request)
    {
        $pendingRegistration = $this->pendingRegistrationFromSession($request);

        if (! $pendingRegistration) {
            return redirect()->route('login')->withErrors(['register' => 'Please start registration first.']);
        }

        return view('auth.verify-otp', [
            'pendingRegistration' => $pendingRegistration,
            'demoOtp' => config('app.debug') ? $request->session()->get('demo_registration_otp') : null,
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingRegistration = $this->pendingRegistrationFromSession($request);

        if (! $pendingRegistration) {
            return redirect()->route('login')->withErrors(['register' => 'Please start registration again.']);
        }

        if ($pendingRegistration->isExpired()) {
            $pendingRegistration->delete();
            $request->session()->forget(['pending_registration_id', 'demo_registration_otp']);

            return redirect()->route('login')->withErrors(['register' => 'The OTP expired. Please register again.']);
        }

        if ($pendingRegistration->otp_code !== $data['otp']) {
            $pendingRegistration->increment('attempts');

            if ($pendingRegistration->attempts >= 4) {
                $pendingRegistration->delete();
                $request->session()->forget(['pending_registration_id', 'demo_registration_otp']);

                return redirect()->route('login')->withErrors(['register' => 'Too many incorrect OTP attempts. Please register again.']);
            }

            return back()->withErrors(['otp' => 'The OTP did not match.'])->onlyInput('otp');
        }

        $user = User::create([
            'name' => $pendingRegistration->name,
            'email' => $pendingRegistration->email,
            'role' => 'customer',
            'email_verified_at' => now(),
            'password' => $pendingRegistration->password,
        ]);

        $pendingRegistration->delete();
        $request->session()->forget(['pending_registration_id', 'demo_registration_otp']);

        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeSessionCart($request);

        return redirect()->route('customer.dashboard')->with('status', 'Welcome to Ashvalian.');
    }

    public function resendRegistrationOtp(Request $request)
    {
        $pendingRegistration = $this->pendingRegistrationFromSession($request);

        if (! $pendingRegistration) {
            return redirect()->route('login')->withErrors(['register' => 'Please start registration again.']);
        }

        $otp = (string) random_int(100000, 999999);
        $pendingRegistration->update([
            'otp_code' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendRegistrationOtp($pendingRegistration);
        $request->session()->put('demo_registration_otp', $otp);

        return back()->with('status', 'A new OTP was sent to '.$pendingRegistration->destination.'.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        $email = strtolower($data['email']);
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->with('status', 'If that email exists, an OTP will be sent.');
        }

        PendingPasswordReset::where('user_id', $user->id)->delete();

        $otp = (string) random_int(100000, 999999);
        $pendingPasswordReset = PendingPasswordReset::create([
            'user_id' => $user->id,
            'email' => $email,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendPasswordResetOtpMail($pendingPasswordReset);

        $request->session()->put('pending_password_reset_id', $pendingPasswordReset->id);
        $request->session()->forget('verified_password_reset_id');
        $request->session()->put('demo_password_reset_otp', $otp);

        return redirect()
            ->route('password.verify')
            ->with('status', 'We sent a 6-digit OTP to '.$pendingPasswordReset->email.'.');
    }

    public function showPasswordResetOtp(Request $request)
    {
        $pendingPasswordReset = $this->pendingPasswordResetFromSession($request);

        if (! $pendingPasswordReset) {
            return redirect()->route('password.forgot')->withErrors(['email' => 'Please request a password reset first.']);
        }

        return view('auth.verify-password-otp', [
            'pendingPasswordReset' => $pendingPasswordReset,
            'demoOtp' => config('app.debug') ? $request->session()->get('demo_password_reset_otp') : null,
        ]);
    }

    public function verifyPasswordResetOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingPasswordReset = $this->pendingPasswordResetFromSession($request);

        if (! $pendingPasswordReset) {
            return redirect()->route('password.forgot')->withErrors(['email' => 'Please request a password reset again.']);
        }

        if ($pendingPasswordReset->isExpired()) {
            $pendingPasswordReset->delete();
            $this->forgetPasswordResetSession($request);

            return redirect()->route('password.forgot')->withErrors(['email' => 'The OTP expired. Please request a new password reset.']);
        }

        if ($pendingPasswordReset->otp_code !== $data['otp']) {
            $pendingPasswordReset->increment('attempts');

            if ($pendingPasswordReset->attempts >= 4) {
                $pendingPasswordReset->delete();
                $this->forgetPasswordResetSession($request);

                return redirect()->route('password.forgot')->withErrors(['email' => 'Too many incorrect OTP attempts. Please request a new password reset.']);
            }

            return back()->withErrors(['otp' => 'The OTP did not match.'])->onlyInput('otp');
        }

        $pendingPasswordReset->update(['verified_at' => now()]);
        $request->session()->put('verified_password_reset_id', $pendingPasswordReset->id);

        return redirect()->route('password.reset')->with('status', 'OTP verified. Choose a new password.');
    }

    public function resendPasswordResetOtp(Request $request)
    {
        $pendingPasswordReset = $this->pendingPasswordResetFromSession($request);

        if (! $pendingPasswordReset) {
            return redirect()->route('password.forgot')->withErrors(['email' => 'Please request a password reset again.']);
        }

        $otp = (string) random_int(100000, 999999);
        $pendingPasswordReset->update([
            'otp_code' => $otp,
            'attempts' => 0,
            'verified_at' => null,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendPasswordResetOtpMail($pendingPasswordReset);
        $request->session()->forget('verified_password_reset_id');
        $request->session()->put('demo_password_reset_otp', $otp);

        return back()->with('status', 'A new OTP was sent to '.$pendingPasswordReset->email.'.');
    }

    public function showResetPassword(Request $request)
    {
        $pendingPasswordReset = $this->verifiedPasswordResetFromSession($request);

        if (! $pendingPasswordReset) {
            return redirect()->route('password.forgot')->withErrors(['email' => 'Please verify the password reset OTP first.']);
        }

        return view('auth.reset-password', [
            'pendingPasswordReset' => $pendingPasswordReset,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $pendingPasswordReset = $this->verifiedPasswordResetFromSession($request);

        if (! $pendingPasswordReset) {
            return redirect()->route('password.forgot')->withErrors(['email' => 'Please verify the password reset OTP first.']);
        }

        if ($pendingPasswordReset->isExpired()) {
            $pendingPasswordReset->delete();
            $this->forgetPasswordResetSession($request);

            return redirect()->route('password.forgot')->withErrors(['email' => 'The password reset expired. Please request a new OTP.']);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $pendingPasswordReset->user->update([
            'password' => Hash::make($data['password']),
        ]);

        $pendingPasswordReset->delete();
        $this->forgetPasswordResetSession($request);
        Auth::logout();

        return redirect()->route('login')->with('status', 'Password updated. You can sign in with your new password.');
    }

    public function redirectToGoogle(Request $request)
    {
        $google = config('services.google');

        if (blank($google['client_id'] ?? null) || blank($google['client_secret'] ?? null) || blank($google['redirect'] ?? null)) {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Google signup needs a Google OAuth client. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.']);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $google['client_id'],
            'redirect_uri' => $google['redirect'],
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors(['google' => 'Google signup was cancelled.']);
        }

        if (! hash_equals((string) $request->session()->pull('google_oauth_state'), (string) $request->query('state'))) {
            return redirect()->route('login')->withErrors(['google' => 'Google signup could not be verified. Please try again.']);
        }

        $google = config('services.google');

        if (blank($google['client_id'] ?? null) || blank($google['client_secret'] ?? null) || blank($google['redirect'] ?? null)) {
            return redirect()->route('login')->withErrors(['google' => 'Google signup needs a Google OAuth client. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.']);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $request->query('code'),
            'client_id' => $google['client_id'],
            'client_secret' => $google['client_secret'],
            'redirect_uri' => $google['redirect'],
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful() || blank($tokenResponse->json('access_token'))) {
            return redirect()->route('login')->withErrors(['google' => 'Google signup failed while requesting access.']);
        }

        $profileResponse = Http::withToken($tokenResponse->json('access_token'))
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $profileResponse->successful() || blank($profileResponse->json('email'))) {
            return redirect()->route('login')->withErrors(['google' => 'Google did not return a verified email address.']);
        }

        $email = strtolower($profileResponse->json('email'));
        $emailVerified = filter_var($profileResponse->json('email_verified'), FILTER_VALIDATE_BOOLEAN);

        if (! $emailVerified) {
            return redirect()->route('login')->withErrors(['google' => 'Please verify your Google email address before signing up.']);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $profileResponse->json('name') ?: Str::before($email, '@'),
                'role' => 'customer',
                'email_verified_at' => now(),
                'password' => Hash::make(Str::password(32)),
            ],
        );

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        $this->mergeSessionCart($request);

        return redirect()->intended($this->redirectPathFor($user))->with('status', 'Signed in with Google.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectPathFor(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'manager' => route('manager.products.index'),
            'delivery_manager' => route('delivery.dashboard'),
            default => route('customer.dashboard'),
        };
    }

    private function pendingRegistrationFromSession(Request $request): ?PendingRegistration
    {
        $id = $request->session()->get('pending_registration_id');

        return $id ? PendingRegistration::find($id) : null;
    }

    private function pendingPasswordResetFromSession(Request $request): ?PendingPasswordReset
    {
        $id = $request->session()->get('pending_password_reset_id');

        return $id ? PendingPasswordReset::with('user')->find($id) : null;
    }

    private function verifiedPasswordResetFromSession(Request $request): ?PendingPasswordReset
    {
        $id = $request->session()->get('pending_password_reset_id');
        $verifiedId = $request->session()->get('verified_password_reset_id');

        if (! $id || (int) $id !== (int) $verifiedId) {
            return null;
        }

        $pendingPasswordReset = PendingPasswordReset::with('user')->find($id);

        return $pendingPasswordReset?->isVerified() ? $pendingPasswordReset : null;
    }

    private function sendRegistrationOtp(PendingRegistration $pendingRegistration): void
    {
        if ($pendingRegistration->email) {
            try {
                Mail::to($pendingRegistration->email)->send(new RegistrationOtpMail($pendingRegistration));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        logger()->info('Ashvalian registration OTP', [
            'destination' => $pendingRegistration->destination,
            'otp' => $pendingRegistration->otp_code,
        ]);
    }

    private function sendPasswordResetOtpMail(PendingPasswordReset $pendingPasswordReset): void
    {
        try {
            Mail::to($pendingPasswordReset->email)->send(new PasswordResetOtpMail($pendingPasswordReset));
        } catch (\Throwable $exception) {
            report($exception);
        }

        logger()->info('Ashvalian password reset OTP', [
            'user_id' => $pendingPasswordReset->user_id,
            'destination' => $pendingPasswordReset->email,
            'otp' => $pendingPasswordReset->otp_code,
        ]);
    }

    private function forgetPasswordResetSession(Request $request): void
    {
        $request->session()->forget([
            'pending_password_reset_id',
            'verified_password_reset_id',
            'demo_password_reset_otp',
        ]);
    }

    private function mergeSessionCart(Request $request): void
    {
        $sessionItems = $request->session()->get('cart.items', []);

        if ($sessionItems === []) {
            return;
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id], ['session_id' => $request->session()->getId()]);

        foreach ($sessionItems as $sessionItem) {
            $productId = $sessionItem['product_id'] ?? null;
            if (!$productId) {
                continue;
            }
            $quantity = max(1, (int) ($sessionItem['quantity'] ?? 1));
            $options = array_filter($sessionItem['options'] ?? []);

            $cartItem = $cart->items()->where('product_id', $productId)->get()->first(function($i) use ($options) {
                return $i->options == $options;
            });

            if (!$cartItem) {
                $cartItem = new \App\Models\CartItem(['product_id' => $productId]);
                $cartItem->cart_id = $cart->id;
                $cartItem->quantity = 0;
            }

            $cartItem->quantity = min(20, $cartItem->quantity + $quantity);
            $cartItem->options = $options;
            $cartItem->save();
        }

        $request->session()->forget('cart.items');
    }
}
