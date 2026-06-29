<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryItemStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_manager_can_move_item_to_next_status(): void
    {
        $deliveryManager = User::factory()->create([
            'role' => 'delivery_manager',
            'email_verified_at' => now(),
        ]);
        $item = $this->orderItemWithStatus('confirmed');

        $this->actingAs($deliveryManager)
            ->from('/delivery/incoming')
            ->patch(route('delivery.order-items.update', $item), [
                'status' => 'processing',
                'tracking_note' => 'Warehouse processing started.',
            ])
            ->assertRedirect('/delivery/incoming')
            ->assertSessionHasNoErrors();

        $item->refresh();

        $this->assertSame('processing', $item->status);
        $this->assertSame('Warehouse processing started.', $item->tracking_note);
        $this->assertNotNull($item->confirmed_at);
    }

    public function test_delivery_manager_cannot_backtrack_item_status(): void
    {
        $deliveryManager = User::factory()->create([
            'role' => 'delivery_manager',
            'email_verified_at' => now(),
        ]);
        $item = $this->orderItemWithStatus('shipped');

        $this->actingAs($deliveryManager)
            ->from('/delivery/incoming')
            ->patch(route('delivery.order-items.update', $item), [
                'status' => 'processing',
                'tracking_note' => 'Trying to move backward.',
            ])
            ->assertRedirect('/delivery/incoming')
            ->assertSessionHasErrors('status');

        $item->refresh();

        $this->assertSame('shipped', $item->status);
        $this->assertNull($item->tracking_note);
    }

    private function orderItemWithStatus(string $status)
    {
        $order = Order::create([
            'order_number' => '#ASH-TEST-'.strtoupper($status),
            'status' => 'confirmed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'subtotal' => 1000,
            'discount_total' => 0,
            'shipping_total' => 80,
            'tax_total' => 50,
            'grand_total' => 1130,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.test',
            'customer_phone' => '+8801700000000',
            'shipping_address' => 'House 12, Road 4, Dhaka',
            'placed_at' => now(),
        ]);

        return $order->items()->create([
            'product_name' => 'Ashvalian Test Item',
            'sku' => 'ASH-TEST-'.$status,
            'unit_price' => 1000,
            'quantity' => 1,
            'line_total' => 1000,
            'status' => $status,
        ]);
    }
}
