<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerAddress;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\ShipmentEvent;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'activity_logs',
            'support_tickets',
            'return_requests',
            'reviews',
            'shipment_events',
            'shipments',
            'order_items',
            'orders',
            'wishlists',
            'cart_items',
            'carts',
            'coupons',
            'customer_addresses',
            'delivery_zones',
            'products',
            'categories',
            'announcements',
            'banners',
            'pending_password_resets',
            'pending_email_changes',
            'pending_registrations',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $admin = User::create([
            'name' => 'Marcus Sterling',
            'email' => 'admin@ashvalian.test',
            'phone' => '+8801700000001',
            'role' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
        ]);

        $manager = User::create([
            'name' => 'Elena Rodriguez',
            'email' => 'manager@ashvalian.test',
            'phone' => '+8801700000002',
            'role' => 'manager',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
        ]);

        $deliveryManager = User::create([
            'name' => 'Julian Vance',
            'email' => 'delivery@ashvalian.test',
            'phone' => '+8801700000003',
            'role' => 'delivery_manager',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
        ]);

        $customer = User::create([
            'name' => 'Adrian Ashval',
            'email' => 'customer@ashvalian.test',
            'phone' => '+8801700000004',
            'role' => 'customer',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
        ]);

        $fitness = Category::create([
            'name' => 'Fitness',
            'slug' => 'fitness',
            'description' => 'Technical apparel engineered for movement and recovery.',
            'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 1,
        ]);

        $fashion = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'description' => 'Quiet luxury pieces designed for everyday performance.',
            'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 2,
        ]);

        $accessories = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => 'Precision accessories for training, recovery, and travel.',
            'image_url' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=1200&q=80',
            'sort_order' => 3,
        ]);

        foreach ([
            ['T-Shirts', 't-shirts', $fitness->id],
            ['Joggers & Pants', 'joggers-pants', $fitness->id],
            ['Compression Gear', 'compression-gear', $fitness->id],
            ['Outerwear', 'outerwear', $fashion->id],
            ['Gym Accessories', 'gym-accessories', $accessories->id],
        ] as [$name, $slug, $parentId]) {
            Category::create(['name' => $name, 'slug' => $slug, 'parent_id' => $parentId]);
        }

        Banner::create([
            'title' => 'Uncompromising Performance.',
            'subtitle' => 'Elite technical apparel engineered for the intersection of high-intensity athletics and high-end aesthetic precision.',
            'image_url' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1800&q=80',
            'cta_label' => 'Shop Now',
            'cta_url' => '/shop/fitness',
        ]);

        $products = [
            [
                'name' => 'Apex Compression Leggings',
                'sku' => 'ASH-ACL-110',
                'category_id' => $fitness->id,
                'price' => 110,
                'cost' => 48,
                'stock_quantity' => 88,
                'primary_image_url' => 'https://images.unsplash.com/photo-1506629905607-d9ba0fbe0cbd?auto=format&fit=crop&w=1000&q=80',
                'is_featured' => true,
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => ['Midnight Black', 'Carbon Grey', 'Azure'],
            ],
            [
                'name' => 'Ashvalian Kinetic Tee',
                'sku' => 'AK-V1-42-BC',
                'category_id' => $fitness->id,
                'price' => 78,
                'cost' => 24,
                'stock_quantity' => 142,
                'primary_image_url' => 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=1000&q=80',
                'is_featured' => true,
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => ['Black', 'Cool Grey', 'Electric Blue'],
            ],
            [
                'name' => 'Apex Elite Runner',
                'sku' => 'AER-RED-185',
                'category_id' => $fitness->id,
                'price' => 185,
                'cost' => 76,
                'stock_quantity' => 2,
                'low_stock_threshold' => 8,
                'primary_image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80',
                'is_featured' => true,
                'sizes' => ['40', '41', '42', '43', '44'],
                'colors' => ['Crimson Tech', 'Black/Azure'],
            ],
            [
                'name' => 'Peak Performance Pant',
                'sku' => 'PPP-125-BLK',
                'category_id' => $fitness->id,
                'price' => 125,
                'cost' => 52,
                'stock_quantity' => 61,
                'primary_image_url' => 'https://images.unsplash.com/photo-1518459031867-a89b944bffe4?auto=format&fit=crop&w=1000&q=80',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => ['Matte Black'],
            ],
            [
                'name' => 'Apex Performance Jacket',
                'sku' => 'APJ-245-PB',
                'category_id' => $fashion->id,
                'price' => 245,
                'cost' => 104,
                'stock_quantity' => 34,
                'primary_image_url' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=1000&q=80',
                'is_featured' => true,
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => ['Phantom Black', 'Silver Grey', 'Cobalt'],
            ],
            [
                'name' => 'Onyx Travel Duffle',
                'sku' => 'OTD-240-BLK',
                'category_id' => $accessories->id,
                'price' => 240,
                'cost' => 93,
                'stock_quantity' => 25,
                'primary_image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1000&q=80',
                'is_featured' => true,
                'sizes' => ['One Size'],
                'colors' => ['Black'],
            ],
            [
                'name' => 'Obsidian Weight Set',
                'sku' => 'OWS-199-OBS',
                'category_id' => $accessories->id,
                'price' => 199,
                'compare_at_price' => 249,
                'cost' => 98,
                'stock_quantity' => 18,
                'primary_image_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1000&q=80',
                'is_on_sale' => true,
                'sizes' => ['Set'],
                'colors' => ['Obsidian'],
            ],
            [
                'name' => 'Stealth Hydro Flask',
                'sku' => 'SHF-045-STL',
                'category_id' => $accessories->id,
                'price' => 45,
                'cost' => 15,
                'stock_quantity' => 74,
                'primary_image_url' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=1000&q=80',
                'sizes' => ['750ml'],
                'colors' => ['Steel', 'Black'],
            ],
        ];

        $createdProducts = collect($products)->map(function (array $product) use ($manager) {
            return Product::create(array_merge([
                'created_by' => $manager->id,
                'slug' => Str::slug($product['name']),
                'description' => 'Engineered for high-intensity training with lightweight support, durable finish, and premium Ashvalian detailing.',
                'low_stock_threshold' => 10,
                'images' => [$product['primary_image_url']],
                'specifications' => [
                    'Material' => '82% recycled performance fiber, 18% elastane',
                    'Weight' => 'Lightweight',
                    'Breathability' => 'Extreme / Ventalux system',
                ],
                'features' => ['Dynamic ventilation', 'Hydro-shield layer', 'Eclipse reflectivity'],
                'rating_average' => 4.8,
                'rating_count' => 124,
                'status' => 'active',
            ], $product));
        });

        $createdProducts->where('is_featured', true)->values()->each(function (Product $product, int $index) {
            $product->update(['featured_sort_order' => $index + 1]);
        });

        $createdProducts->where('is_on_sale', true)->values()->each(function (Product $product, int $index) {
            $product->update(['sale_sort_order' => $index + 1]);
        });

        DeliveryZone::insert([
            ['name' => 'Dhaka City', 'fee' => 80, 'estimated_days' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chattogram Metro', 'fee' => 120, 'estimated_days' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outside Metro', 'fee' => 180, 'estimated_days' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        CustomerAddress::create([
            'user_id' => $customer->id,
            'delivery_zone_id' => 1,
            'label' => 'Home',
            'first_name' => 'Adrian',
            'last_name' => 'Ashval',
            'phone' => $customer->phone,
            'street_address' => 'House 12, Road 4, Sector 7',
            'city' => 'Dhaka',
            'postal_code' => '1212',
            'country' => 'Bangladesh',
            'is_default' => true,
        ]);

        Coupon::create([
            'code' => 'ELITE24',
            'name' => 'Elite Performance Sale',
            'type' => 'percent',
            'value' => 20,
            'minimum_order_amount' => 100,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'delivery_zone_id' => 1,
            'order_number' => '#ASH-29410-V',
            'status' => 'shipped',
            'payment_method' => 'sslcommerz',
            'payment_status' => 'paid',
            'subtotal' => 185,
            'shipping_total' => 80,
            'tax_total' => 14.80,
            'grand_total' => 279.80,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'shipping_address' => 'House 12, Road 4, Sector 7, Dhaka City',
            'placed_at' => now()->subDays(3),
        ]);

        $runner = $createdProducts->firstWhere('sku', 'AER-RED-185');
        $order->items()->create([
            'product_id' => $runner->id,
            'product_name' => $runner->name,
            'sku' => $runner->sku,
            'unit_price' => $runner->price,
            'quantity' => 1,
            'status' => 'shipped',
            'tracking_note' => 'Released to Ashvalian Fleet.',
            'confirmed_at' => now()->subDays(2),
            'shipped_at' => now()->subDay(),
            'line_total' => $runner->price,
            'options' => ['Size' => '44', 'Color' => 'Crimson Tech'],
        ]);

        $shipment = $order->shipment()->create([
            'delivery_manager_id' => $deliveryManager->id,
            'tracking_number' => 'TRK-ASH29410',
            'carrier' => 'Ashvalian Fleet',
            'status' => 'in_transit',
            'tracking_notes' => 'Warehouse pickup completed. Package in premium handling lane.',
            'shipped_at' => now()->subDay(),
            'estimated_delivery_at' => now()->addDays(2),
        ]);

        foreach ([
            ['pending', 'Dhaka', 'Order placed and awaiting confirmation', now()->subDays(3)],
            ['confirmed', 'Dhaka', 'Payment verified and order confirmed', now()->subDays(2)],
            ['shipped', 'Dhaka Hub', 'Package released to fleet', now()->subDay()],
        ] as [$status, $location, $notes, $date]) {
            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $status,
                'location' => $location,
                'notes' => $notes,
                'occurred_at' => $date,
            ]);
        }

        $tee = $createdProducts->firstWhere('sku', 'AK-V1-42-BC');
        $duffle = $createdProducts->firstWhere('sku', 'OTD-240-BLK');
        $flask = $createdProducts->firstWhere('sku', 'SHF-045-STL');

        $dispatchOrder = Order::create([
            'user_id' => $customer->id,
            'delivery_zone_id' => 2,
            'order_number' => '#ASH-10293',
            'status' => 'confirmed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'subtotal' => $tee->price,
            'shipping_total' => 120,
            'tax_total' => 6.24,
            'grand_total' => $tee->price + 126.24,
            'customer_name' => 'Julian Martinez',
            'customer_email' => 'julian@example.com',
            'customer_phone' => '+8801711111111',
            'shipping_address' => 'Road 9, Chattogram Metro',
            'placed_at' => now()->subHours(8),
        ]);

        $dispatchOrder->items()->create([
            'product_id' => $tee->id,
            'product_name' => $tee->name,
            'sku' => $tee->sku,
            'unit_price' => $tee->price,
            'quantity' => 1,
            'line_total' => $tee->price,
            'status' => 'pending',
            'options' => ['Size' => 'L', 'Color' => 'Black'],
        ]);

        $dispatchShipment = $dispatchOrder->shipment()->create([
            'delivery_manager_id' => $deliveryManager->id,
            'tracking_number' => 'TRK-ASH10293',
            'carrier' => 'Ashvalian Fleet',
            'status' => 'pending_dispatch',
            'tracking_notes' => 'Awaiting warehouse release.',
            'estimated_delivery_at' => now()->addDays(3),
        ]);

        ShipmentEvent::create([
            'shipment_id' => $dispatchShipment->id,
            'status' => 'pending_dispatch',
            'location' => 'Chattogram Hub',
            'notes' => 'Ready for dispatch confirmation.',
            'occurred_at' => now()->subHours(6),
        ]);

        $deliveredOrder = Order::create([
            'user_id' => $customer->id,
            'delivery_zone_id' => 1,
            'order_number' => '#ASH-28901-T',
            'status' => 'delivered',
            'payment_method' => 'bkash',
            'payment_status' => 'paid',
            'subtotal' => $duffle->price,
            'shipping_total' => 80,
            'tax_total' => 19.20,
            'grand_total' => $duffle->price + 99.20,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'shipping_address' => 'House 12, Road 4, Sector 7, Dhaka City',
            'placed_at' => now()->subDays(9),
            'delivered_at' => now(),
        ]);

        $deliveredOrder->items()->create([
            'product_id' => $duffle->id,
            'product_name' => $duffle->name,
            'sku' => $duffle->sku,
            'unit_price' => $duffle->price,
            'quantity' => 1,
            'line_total' => $duffle->price,
            'status' => 'delivered',
            'tracking_note' => 'Delivered to customer.',
            'confirmed_at' => now()->subDays(8),
            'shipped_at' => now()->subDays(7),
            'delivered_at' => now(),
            'options' => ['Size' => 'One Size', 'Color' => 'Black'],
        ]);

        $deliveredShipment = $deliveredOrder->shipment()->create([
            'delivery_manager_id' => $deliveryManager->id,
            'tracking_number' => 'TRK-ASH28901',
            'carrier' => 'Ashvalian Fleet',
            'status' => 'delivered',
            'tracking_notes' => 'Delivered with signature confirmation.',
            'shipped_at' => now()->subDays(7),
            'estimated_delivery_at' => now()->subDays(4),
            'delivered_at' => now(),
        ]);

        ShipmentEvent::create([
            'shipment_id' => $deliveredShipment->id,
            'status' => 'delivered',
            'location' => 'Dhaka City',
            'notes' => 'Package delivered successfully.',
            'occurred_at' => now(),
        ]);

        $failedOrder = Order::create([
            'user_id' => $customer->id,
            'delivery_zone_id' => 3,
            'order_number' => '#ASH-98002',
            'status' => 'failed',
            'payment_method' => 'sslcommerz',
            'payment_status' => 'paid',
            'subtotal' => $flask->price,
            'shipping_total' => 180,
            'tax_total' => 3.60,
            'grand_total' => $flask->price + 183.60,
            'customer_name' => 'Sarah Kern',
            'customer_email' => 'sarah@example.com',
            'customer_phone' => '+8801722222222',
            'shipping_address' => 'Holding address missing apartment number, Outside Metro',
            'placed_at' => now()->subDays(5),
        ]);

        $failedOrder->items()->create([
            'product_id' => $flask->id,
            'product_name' => $flask->name,
            'sku' => $flask->sku,
            'unit_price' => $flask->price,
            'quantity' => 1,
            'line_total' => $flask->price,
            'status' => 'failed',
            'tracking_note' => 'Recipient unavailable after retry.',
            'confirmed_at' => now()->subDays(4),
            'shipped_at' => now()->subDays(3),
            'options' => ['Size' => '750ml', 'Color' => 'Steel'],
        ]);

        $failedShipment = $failedOrder->shipment()->create([
            'delivery_manager_id' => $deliveryManager->id,
            'tracking_number' => 'TRK-ASH98002',
            'carrier' => 'Ashvalian Fleet',
            'status' => 'failed',
            'tracking_notes' => 'Recipient unavailable. Awaiting retry or return decision.',
            'shipped_at' => now()->subDays(3),
            'estimated_delivery_at' => now()->subDay(),
        ]);

        ShipmentEvent::create([
            'shipment_id' => $failedShipment->id,
            'status' => 'failed',
            'location' => 'Outside Metro',
            'notes' => 'Final delivery attempt failed.',
            'occurred_at' => now()->subDay(),
        ]);

        $createdProducts->take(3)->each(function (Product $product, int $index) use ($customer, $order) {
            Review::create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'rating' => 5,
                'title' => ['Best compression I have worn', 'Perfect for leg day', 'Superior comfort'][$index],
                'body' => 'The material feels premium and holds shape after every session. Pure luxury performance.',
                'is_featured' => true,
            ]);
        });

        ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'product_id' => $runner->id,
            'reason' => 'Incorrect Address: Street missing',
            'details' => 'Customer requested delivery retry with corrected address.',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'user_id' => $customer->id,
            'ticket_number' => 'TKT-ELITE01',
            'category' => 'Order Tracking',
            'subject' => 'Delivery timeline request',
            'message' => 'Please confirm whether my shipment will arrive before the weekend.',
        ]);

        Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Summer Performance Sale',
            'message' => '20% off all elite technical apparel using code ELITE24.',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        ActivityLog::insert([
            ['user_id' => $admin->id, 'action' => 'login', 'description' => 'Admin portal login successful.', 'ip_address' => '127.0.0.1', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $manager->id, 'action' => 'product.created', 'description' => 'Featured product layout updated.', 'ip_address' => '127.0.0.1', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $deliveryManager->id, 'action' => 'shipment.updated', 'description' => 'Warehouse pickup completed for #ASH-29410-V.', 'ip_address' => '127.0.0.1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
