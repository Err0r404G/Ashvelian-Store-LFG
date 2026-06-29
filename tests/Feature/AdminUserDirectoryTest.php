<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_users_page_is_view_and_restriction_only(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk()
            ->assertSee('User Directory')
            ->assertSee('Name')
            ->assertSee('Phone')
            ->assertSee('Email')
            ->assertSee('Role')
            ->assertSee('Status')
            ->assertDontSee('Create Account')
            ->assertDontSee('New password optional')
            ->assertDontSee('Delete')
            ->assertDontSee('Save');
    }

    public function test_admin_users_page_does_not_expose_create_update_or_delete_routes(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $customer = User::where('role', 'customer')->firstOrFail();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Blocked Customer',
            'email' => 'blocked-customer@example.com',
            'role' => 'customer',
            'password' => 'Password123!',
        ])->assertStatus(405);

        $this->actingAs($admin)->put('/admin/users/'.$customer->id, [
            'name' => 'Edited Name',
            'email' => $customer->email,
            'role' => 'customer',
        ])->assertStatus(404);

        $this->actingAs($admin)->delete('/admin/users/'.$customer->id)->assertStatus(404);

        $this->assertDatabaseMissing('users', ['email' => 'blocked-customer@example.com']);
        $this->assertDatabaseMissing('users', ['name' => 'Edited Name']);
        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_staff_create_page_only_allows_staff_roles(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->get('/admin/staff/create')
            ->assertOk()
            ->assertSee('Create Staff Account')
            ->assertSee('Admin')
            ->assertSee('Manager')
            ->assertSee('Delivery Manager')
            ->assertDontSee('<option value="customer"', false);

        $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'Customer Attempt',
            'email' => 'customer-attempt@example.com',
            'role' => 'customer',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('role');

        $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'New Manager',
            'email' => 'new-manager@example.com',
            'phone' => '+8801700000999',
            'role' => 'manager',
            'password' => 'Password123!',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['email' => 'customer-attempt@example.com']);
        $this->assertDatabaseHas('users', [
            'email' => 'new-manager@example.com',
            'role' => 'manager',
        ]);
    }
}
