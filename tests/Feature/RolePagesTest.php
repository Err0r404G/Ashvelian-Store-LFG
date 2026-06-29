<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_management_pages_load(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        foreach (['/admin/dashboard', '/admin/users', '/admin/staff/create', '/admin/coupons', '/admin/announcements', '/admin/reports', '/portal/profile'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertDontSee('/manager/homepage', false)
            ->assertDontSee('/manager/products', false)
            ->assertDontSee('/manager/categories', false)
            ->assertDontSee('/manager/delivery-zones', false)
            ->assertDontSee('/manager/reviews', false)
            ->assertDontSee('/manager/returns', false)
            ->assertDontSee('/delivery/dashboard', false);
    }

    public function test_admin_cannot_access_manager_or_delivery_workspaces(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        foreach (['/manager/homepage', '/manager/products', '/manager/categories', '/manager/delivery-zones', '/manager/reviews', '/manager/returns', '/delivery/dashboard', '/delivery/active'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertForbidden();
        }
    }

    public function test_manager_management_pages_load(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        foreach (['/manager/homepage', '/manager/products', '/manager/categories', '/manager/delivery-zones', '/manager/reviews', '/manager/returns', '/portal/profile'] as $uri) {
            $this->actingAs($manager)->get($uri)->assertOk();
        }
    }

    public function test_delivery_management_pages_load(): void
    {
        $deliveryManager = User::where('role', 'delivery_manager')->firstOrFail();
        $shipment = Shipment::firstOrFail();

        foreach (['/delivery/dashboard', '/delivery/incoming', '/delivery/dispatch', '/delivery/active', '/delivery/returns', '/delivery/history', '/delivery/summary', '/delivery/shipments/'.$shipment->id, '/portal/profile'] as $uri) {
            $this->actingAs($deliveryManager)->get($uri)->assertOk();
        }
    }

    public function test_customer_pages_load(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $product = Product::where('status', 'active')->firstOrFail();
        $order = Order::where('user_id', $customer->id)->firstOrFail();

        foreach (['/shop', '/account', '/account/profile', '/account/addresses', '/account/reviews', '/wishlist', '/cart', '/orders/'.$order->id.'/confirmation', '/orders/'.$order->id.'/invoice', '/products/'.$product->slug] as $uri) {
            $this->actingAs($customer)->get($uri)->assertOk();
        }

        $this->actingAs($customer)
            ->get('/orders/'.$order->id.'/invoice')
            ->assertSee('invoice-print-area', false)
            ->assertSee('no-print', false);
    }
}
