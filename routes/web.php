<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Delivery\DashboardController as DeliveryDashboardController;
use App\Http\Controllers\Manager\CategoryController as ManagerCategoryController;
use App\Http\Controllers\Manager\DeliveryZoneController as ManagerDeliveryZoneController;
use App\Http\Controllers\Manager\HomepageController as ManagerHomepageController;
use App\Http\Controllers\Manager\ProductController as ManagerProductController;
use App\Http\Controllers\Manager\ReturnRequestController as ManagerReturnRequestController;
use App\Http\Controllers\Manager\ReviewController as ManagerReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PortalProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop.index');
Route::get('/shop/{slug}', [StorefrontController::class, 'category'])->name('category.show');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/products/{slug}/size-guide', [StorefrontController::class, 'sizeGuide'])->name('products.size-guide');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/register/verify', [AuthController::class, 'showRegistrationOtp'])->name('register.verify');
Route::post('/register/verify', [AuthController::class, 'verifyRegistrationOtp'])->name('register.verify.store');
Route::post('/register/resend', [AuthController::class, 'resendRegistrationOtp'])->name('register.resend');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetOtp'])->name('password.email');
Route::get('/forgot-password/verify', [AuthController::class, 'showPasswordResetOtp'])->name('password.verify');
Route::post('/forgot-password/verify', [AuthController::class, 'verifyPasswordResetOtp'])->name('password.verify.store');
Route::post('/forgot-password/resend', [AuthController::class, 'resendPasswordResetOtp'])->name('password.resend');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon');

Route::middleware(['auth', 'active.customer', 'role:customer'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/account', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/account/profile', [CustomerProfileController::class, 'edit'])->name('customer.profile.edit');
    Route::patch('/account/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::patch('/account/password', [CustomerProfileController::class, 'password'])->name('customer.password.update');
    Route::post('/account/profile/email', [CustomerProfileController::class, 'requestEmailChange'])->name('customer.profile.email.request');
    Route::get('/account/profile/email/verify', [CustomerProfileController::class, 'showEmailChangeOtp'])->name('customer.profile.email.verify');
    Route::post('/account/profile/email/verify', [CustomerProfileController::class, 'verifyEmailChangeOtp'])->name('customer.profile.email.verify.store');
    Route::post('/account/profile/email/resend', [CustomerProfileController::class, 'resendEmailChangeOtp'])->name('customer.profile.email.resend');
    Route::get('/account/addresses', [CustomerAddressController::class, 'index'])->name('customer.addresses.index');
    Route::post('/account/addresses', [CustomerAddressController::class, 'store'])->name('customer.addresses.store');
    Route::patch('/account/addresses/{address}', [CustomerAddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('/account/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('customer.addresses.destroy');
    Route::get('/account/reviews', [CustomerReviewController::class, 'index'])->name('customer.reviews.index');
    Route::post('/products/{product}/reviews', [CustomerReviewController::class, 'store'])->name('customer.reviews.store');
    Route::patch('/account/reviews/{review}', [CustomerReviewController::class, 'update'])->name('customer.reviews.update');
    Route::delete('/account/reviews/{review}', [CustomerReviewController::class, 'destroy'])->name('customer.reviews.destroy');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support.tickets.store');
});

Route::middleware(['auth', 'role:admin,manager,delivery_manager'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/profile', [PortalProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::patch('/password', [PortalProfileController::class, 'password'])->name('password.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
    Route::resource('users', AdminUserController::class)->only(['index']);
    Route::patch('/customers/{user}/restriction', [AdminUserController::class, 'toggleRestriction'])->name('customers.restriction');
    Route::resource('coupons', AdminCouponController::class)->except(['create', 'show', 'edit']);
    Route::resource('announcements', AdminAnnouncementController::class)->except(['create', 'show', 'edit']);
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/homepage', [ManagerHomepageController::class, 'index'])->name('homepage.index');
    Route::patch('/homepage', [ManagerHomepageController::class, 'update'])->name('homepage.update');
    Route::post('/homepage/banners', [ManagerHomepageController::class, 'storeBanner'])->name('homepage.banners.store');
    Route::put('/homepage/banners/{banner}', [ManagerHomepageController::class, 'updateBanner'])->name('homepage.banners.update');
    Route::delete('/homepage/banners/{banner}', [ManagerHomepageController::class, 'destroyBanner'])->name('homepage.banners.destroy');
    Route::post('/homepage/announcements', [ManagerHomepageController::class, 'storeAnnouncement'])->name('homepage.announcements.store');
    Route::put('/homepage/announcements/{announcement}', [ManagerHomepageController::class, 'updateAnnouncement'])->name('homepage.announcements.update');
    Route::delete('/homepage/announcements/{announcement}', [ManagerHomepageController::class, 'destroyAnnouncement'])->name('homepage.announcements.destroy');
    Route::resource('products', ManagerProductController::class)->except(['show']);
    Route::patch('/products/{product}/availability', [ManagerProductController::class, 'toggleAvailability'])->name('products.availability');
    Route::resource('categories', ManagerCategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('delivery-zones', ManagerDeliveryZoneController::class)
        ->parameters(['delivery-zones' => 'deliveryZone'])
        ->except(['create', 'show', 'edit']);
    Route::get('/reviews', [ManagerReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}', [ManagerReviewController::class, 'update'])->name('reviews.update');
    Route::get('/returns', [ManagerReturnRequestController::class, 'index'])->name('returns.index');
    Route::patch('/returns/{return}', [ManagerReturnRequestController::class, 'update'])->name('returns.update');
});

Route::middleware(['auth', 'role:delivery_manager'])->prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/dashboard', [DeliveryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/incoming', [DeliveryDashboardController::class, 'incoming'])->name('incoming');
    Route::get('/dispatch', [DeliveryDashboardController::class, 'dispatch'])->name('dispatch');
    Route::get('/active', [DeliveryDashboardController::class, 'active'])->name('active');
    Route::get('/returns', [DeliveryDashboardController::class, 'returns'])->name('returns');
    Route::get('/history', [DeliveryDashboardController::class, 'history'])->name('history');
    Route::get('/summary', [DeliveryDashboardController::class, 'summary'])->name('summary');
    Route::get('/shipments/{shipment}', [DeliveryDashboardController::class, 'show'])->name('shipments.show');
    Route::patch('/shipments/{shipment}', [DeliveryDashboardController::class, 'update'])->name('shipments.update');
    Route::patch('/order-items/{orderItem}', [DeliveryDashboardController::class, 'updateItem'])->name('order-items.update');
});
