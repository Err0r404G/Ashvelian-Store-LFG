<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\DeliveryZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantStockTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_manager_can_save_product_options_and_variant_stock(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $category = Category::where('is_active', true)->firstOrFail();

        $response = $this->actingAs($manager)
            ->post(route('manager.products.store'), [
                'category_id' => $category->id,
                'name' => 'Variant Leggings',
                'sku' => 'VAR-LEG-123',
                'description' => 'Sleek variant leggings.',
                'price' => 2500.00,
                'cost' => 1200.00,
                'stock_quantity' => 8,
                'low_stock_threshold' => 2,
                'status' => 'active',
                'sizes' => ['XL', 'XS'],
                'colors' => ['White', 'Blue'],
                'variant_stock' => [
                    'White - XL' => ['qty' => 5, 'image' => null],
                    'Blue - XS' => ['qty' => 3, 'image' => null],
                ],
            ]);

        $response->assertRedirect(route('manager.products.index'));

        $product = Product::where('sku', 'VAR-LEG-123')->firstOrFail();
        $this->assertSame(['XL', 'XS'], $product->sizes);
        $this->assertSame(['White', 'Blue'], $product->colors);
        $this->assertSame([
            'White - XL' => ['qty' => 5, 'image' => null],
            'Blue - XS' => ['qty' => 3, 'image' => null],
        ], $product->variant_stock);
        $this->assertEquals(8, $product->stock_quantity);
    }

    public function test_checkout_decrements_variant_stock_correctly(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::firstOrFail();
        $zone = DeliveryZone::firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Apex Runners',
            'slug' => 'apex-runners',
            'sku' => 'APEX-RUN-V',
            'description' => 'Fast runners.',
            'price' => 150.00,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'status' => 'active',
            'sizes' => ['XL', 'XS'],
            'colors' => ['White', 'Blue'],
            'variant_stock' => [
                'White - XL' => ['qty' => 6, 'image' => 'http://example.com/white-xl.jpg'],
                'Blue - XS' => ['qty' => 4, 'image' => null],
            ],
        ]);

        // Mock adding to cart and checkout
        $this->actingAs($customer)
            ->post(route('cart.add', $product), [
                'quantity' => 2,
                'size' => 'XL',
                'color' => 'White',
            ])
            ->assertRedirect();

        $response = $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'email' => 'customer@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'street_address' => '123 Elite St',
                'delivery_zone_id' => $zone->id,
                'phone' => '12345678',
                'payment_method' => 'cod',
            ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);
        $this->assertSame([
            'White - XL' => ['qty' => 4, 'image' => 'http://example.com/white-xl.jpg'],
            'Blue - XS' => ['qty' => 4, 'image' => null],
        ], $product->variant_stock);
    }
}
