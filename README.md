# Ashvalian Store

Ashvalian Store is a Laravel, Blade, MySQL, Bootstrap, and AJAX e-commerce management system with separate workspaces for Admin, Manager, Delivery Manager, and Customer roles.

## Stack

- Laravel 12
- Blade MVC views
- MySQL / MariaDB
- Bootstrap 5
- Custom CSS and lightweight AJAX
- XAMPP-compatible local setup

## Local Setup

1. Start Apache and MySQL from XAMPP.
2. Create the database if it does not exist:

   ```sql
   CREATE DATABASE ashvalian CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Install PHP dependencies with Composer.
4. Copy `.env.example` to `.env` and confirm these values:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ashvalian
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Run:

   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan storage:link
   php artisan serve --host=127.0.0.1 --port=8000
   ```

## Demo Accounts

All seeded demo users use this password:

```text
Password123!
```

- Admin: `admin@ashvalian.test`
- Manager: `manager@ashvalian.test`
- Delivery Manager: `delivery@ashvalian.test`
- Customer: `customer@ashvalian.test`

## MVC Map

- Models: `app/Models`
- Controllers: `app/Http/Controllers`
- Role middleware: `app/Http/Middleware`
- Routes: `routes/web.php`
- Blade views: `resources/views`
- Custom assets: `public/css/ashvalian.css`, `public/js/ashvalian.js`
- Database schema: `database/migrations`
- Demo data: `database/seeders/DatabaseSeeder.php`

## Included Feature Foundation

- Role-based login and redirects
- Customer storefront, product listing, product details, cart, wishlist, checkout, invoice, and order tracking
- Admin analytics dashboard, staff creation, customer restriction hook, activity logs
- Manager product inventory dashboard, create/edit products, soft delete protection for pending orders
- Delivery dashboard, active shipment tracking, failed/returned shipment queue, shipment status updates
- Coupons with AJAX validation
- Seeded products, categories, reviews, order, shipment timeline, support ticket, and announcement data

## Portfolio Screenshots

These snapshots highlight the customer storefront, role dashboards, and the most important operational workflows.

### Storefront and Customer Experience

| Home Page | Product Details |
| --- | --- |
| ![Ashvalian storefront home page](Screen/5.png) | ![Product detail page with gallery, variants, reviews, and recommendations](Screen/4.png) |

| Shop Catalog | Product Purchase Flow |
| --- | --- |
| ![Shop catalog with categories, filters, and product grid](Screen/14.png) | ![Product page focused on variant selection and purchase controls](Screen/13.png) |

| Wishlist | Wishlist Remove Confirmation |
| --- | --- |
| ![Customer wishlist page with saved products](Screen/12.png) | ![Wishlist remove confirmation modal](Screen/15.png) |

| Shopping Cart | Checkout |
| --- | --- |
| ![Shopping cart with coupon entry, totals, and recommendations](Screen/16.png) | ![Checkout page with shipping address, payment method, and order summary](Screen/6.png) |

### Authentication and Customer Portal

| Login | Customer Dashboard |
| --- | --- |
| ![Login page with email, password, Google, and Facebook sign-in options](Screen/8.png) | ![Customer dashboard with order history, support, wishlist, and recommendations](Screen/11.png) |

### Admin and Manager Workspaces

| Admin Overview | Product Management |
| --- | --- |
| ![Admin dashboard with revenue, orders, users, and trend widgets](Screen/3.png) | ![Manager product management dashboard with inventory and moderation controls](Screen/10.png) |

| Add Product Modal | Manager Product Workflow |
| --- | --- |
| ![Add product modal with product details, pricing, inventory, and image upload](Screen/2.png) | ![Manager workflow for maintaining featured products and storefront content](Screen/10.png) |

### Delivery and Fulfillment

| Logistics Dashboard | Active Deliveries |
| --- | --- |
| ![Delivery logistics dashboard with shipment metrics and pending orders](Screen/9.png) | ![Active deliveries dashboard with tracking table and shipment map](Screen/1.png) |

| Failed and Returned Shipments |
| --- |
| ![Failed and returned shipments queue with resolution actions](Screen/7.png) |

## Notes

The invoice route renders printable HTML with browser print/save-PDF support. A dedicated PDF generator such as Dompdf can be added later if binary PDF downloads are required from the server.
# Ashvelian-Store-LFG
