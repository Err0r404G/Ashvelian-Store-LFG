<?php

namespace Tests\Feature;

use App\Models\PendingEmailChange;
use App\Models\PendingPasswordReset;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_registration_requires_otp_before_user_is_created(): void
    {
        $this->post('/register', [
            'name' => 'Nadia Customer',
            'email' => 'nadia@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/register/verify');

        $this->assertDatabaseMissing('users', ['email' => 'nadia@example.com']);

        $pending = PendingRegistration::where('email', 'nadia@example.com')->firstOrFail();

        $this->withSession(['pending_registration_id' => $pending->id])
            ->post('/register/verify', ['otp' => $pending->otp_code])
            ->assertRedirect('/account');

        $this->assertDatabaseHas('users', [
            'email' => 'nadia@example.com',
            'role' => 'customer',
        ]);

        $this->assertAuthenticated();
    }

    public function test_phone_registration_and_phone_login_are_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Phone Customer',
            'email' => '+8801777777777',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => '+8801777777777',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('pending_registrations', 0);
    }

    public function test_wrong_otp_does_not_create_user(): void
    {
        $this->post('/register', [
            'name' => 'Wrong Otp',
            'email' => 'wrong-otp@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/register/verify');

        $pending = PendingRegistration::where('email', 'wrong-otp@example.com')->firstOrFail();

        $this->withSession(['pending_registration_id' => $pending->id])
            ->post('/register/verify', ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertDatabaseMissing('users', ['email' => 'wrong-otp@example.com']);
    }

    public function test_customer_email_change_requires_password_and_otp(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => 'current@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->actingAs($user)->post('/account/profile/email', [
            'email' => 'fresh@example.com',
            'current_password' => 'Password123!',
        ])->assertRedirect('/account/profile/email/verify');

        $this->assertDatabaseHas('users', ['email' => 'current@example.com']);

        $pending = PendingEmailChange::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->withSession(['pending_email_change_id' => $pending->id])
            ->post('/account/profile/email/verify', ['otp' => $pending->otp_code])
            ->assertRedirect('/account/profile');

        $user->refresh();

        $this->assertSame('fresh@example.com', $user->email);
        $this->assertNotNull($user->email_changed_at);
        $this->assertDatabaseMissing('pending_email_changes', ['id' => $pending->id]);
    }

    public function test_customer_email_change_is_blocked_for_30_days(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => 'cooldown@example.com',
            'email_changed_at' => now()->subDays(5),
            'password' => Hash::make('Password123!'),
        ]);

        $this->actingAs($user)->post('/account/profile/email', [
            'email' => 'too-soon@example.com',
            'current_password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('pending_email_changes', 0);
    }

    public function test_forgot_password_requires_email_otp_before_password_change(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $this->post('/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertRedirect('/forgot-password/verify');

        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));

        $pending = PendingPasswordReset::where('user_id', $user->id)->firstOrFail();

        $this->withSession(['pending_password_reset_id' => $pending->id])
            ->post('/forgot-password/verify', ['otp' => $pending->otp_code])
            ->assertRedirect('/reset-password');

        $pending->refresh();
        $this->assertNotNull($pending->verified_at);

        $this->withSession([
            'pending_password_reset_id' => $pending->id,
            'verified_password_reset_id' => $pending->id,
        ])->post('/reset-password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertDatabaseMissing('pending_password_resets', ['id' => $pending->id]);
    }

    public function test_unknown_forgot_password_email_does_not_create_otp(): void
    {
        $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertRedirect('/forgot-password');

        $this->assertDatabaseCount('pending_password_resets', 0);
    }

    public function test_password_cannot_be_reset_before_otp_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked-reset@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $pending = PendingPasswordReset::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->withSession(['pending_password_reset_id' => $pending->id])
            ->post('/reset-password', [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])->assertRedirect('/forgot-password');

        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }
}
