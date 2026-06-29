<?php

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewWindowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_reviews_page_uses_vertical_list_layout(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();

        $response = $this->actingAs($customer)->get('/account/reviews');

        $response->assertOk()
            ->assertSee('review-list', false)
            ->assertSee('Your Reviews')
            ->assertDontSee('<table', false);
    }

    public function test_customer_can_review_delivered_product_within_30_days(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $product = Product::where('sku', 'OTD-240-BLK')->firstOrFail();

        Review::where('user_id', $customer->id)->where('product_id', $product->id)->delete();

        $this->actingAs($customer)
            ->from('/account/reviews')
            ->post('/products/'.$product->id.'/reviews', [
                'rating' => 5,
                'title' => 'Excellent delivery',
                'body' => 'The product arrived perfectly and the quality is excellent.',
            ])
            ->assertRedirect('/account/reviews')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'Excellent delivery',
        ]);
    }

    public function test_customer_cannot_create_review_after_30_day_window_expires(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $product = Product::where('sku', 'OTD-240-BLK')->firstOrFail();
        $orderItem = OrderItem::where('product_id', $product->id)->where('status', 'delivered')->firstOrFail();

        Review::where('user_id', $customer->id)->where('product_id', $product->id)->delete();
        $orderItem->update(['delivered_at' => now()->subDays(31)]);
        $orderItem->order->update(['delivered_at' => now()->subDays(31)]);

        $this->actingAs($customer)
            ->from('/account/reviews')
            ->post('/products/'.$product->id.'/reviews', [
                'rating' => 5,
                'title' => 'Too late',
                'body' => 'This should not be accepted after the review window.',
            ])
            ->assertRedirect('/account/reviews')
            ->assertSessionHasErrors('product');

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'Too late',
        ]);
    }
}
