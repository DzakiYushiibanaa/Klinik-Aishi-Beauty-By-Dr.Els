<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PraKonsulController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ============================
    // MEMBER REGISTRATION
    // ============================
    Route::get('/member/daftar', [App\Http\Controllers\MemberRegistrationController::class, 'create'])->name('member.register');
    Route::post('/member/daftar', [App\Http\Controllers\MemberRegistrationController::class, 'store'])->name('member.store');

// ============================
// SHOP
// ============================
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

// ============================
// TREATMENT 
// ============================
// 
Route::get('/treatment', [TreatmentController::class, 'index'])->name('treatment.index');
Route::get('/treatment/{treatment}', [TreatmentController::class, 'show'])->name('treatment.show');

// ============================
// BUNDLE 
// ============================
// 
Route::get('/bundle', [BundleController::class, 'index'])->name('bundle.index');
Route::get('/bundle/{bundle}', [BundleController::class, 'show'])->name('bundle.show');

Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/{id}', [ForumController::class, 'show'])
    ->name('forum.show')
    ->whereNumber('id');

// ============================
// AUTH REQUIRED AREA
// ============================
Route::middleware(['auth'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('/profile/update', [DashboardController::class, 'updateProfile'])->name('pasien.profile.update');

    // Route untuk Notifikasi
    Route::get('/notification/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
    Route::post('/notification/read-all', [NotificationController::class, 'markAllRead'])->name('notification.readAll');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================
    // CART
    // ============================
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-bundle/{bundle}', [CartController::class, 'addBundle'])->name('cart.add_bundle');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');

    // ============================
    // CHECKOUT
    // ============================
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // ============================
    // PRA KONSUL
    // ============================
    Route::get('/pra-konsul', [PraKonsulController::class, 'index'])->name('pra.index');
    Route::get('/pra-konsul/buat', [PraKonsulController::class, 'create'])->name('pra.create');
    Route::post('/pra-konsul', [PraKonsulController::class, 'store'])->name('pra.store');
    Route::get('/pra-konsul/{form}', [PraKonsulController::class, 'show'])->name('pra.show');

    // ============================
    // APPOINTMENT (JANJI TEMU)
    // ============================
    // Halaman Pilih Layanan
    Route::get('/appointment/pilih', [AppointmentController::class, 'selectType'])->name('appointment.select');
    // Halaman Wizard Konsultasi Dokter
    Route::get('/appointment/konsultasi', [AppointmentController::class, 'konsultasi'])->name('appointment.konsultasi');
    Route::post('/appointment/konsultasi', [AppointmentController::class, 'submitKonsultasi'])->name('appointment.konsultasi.store');
    // Halaman Form Booking (lama/treatment)
    Route::get('/appointment/buat', [AppointmentController::class, 'create'])->name('appointment.create');
    // Proses Simpan Booking
    Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment.store');
    // list appointment pasien
    Route::get('/appointment', [AppointmentController::class, 'index'])
        ->name('appointment.index');
    Route::patch('/appointment/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');


    // API Routes (Komerce)
    // API WILAYAH (Sesuai gaya SantriKoding)
    Route::get('/api/provinces', [DashboardController::class, 'getProvinces'])->name('api.provinces');
    Route::get('/api/cities/{provinceId}', [DashboardController::class, 'getCities']);
    Route::get('/api/districts/{cityId}', [DashboardController::class, 'getDistricts']); 
    
    Route::post('/api/check-ongkir', [CheckoutController::class, 'checkOngkir'])->name('api.checkOngkir');
    Route::post('/api/check-promo', [CheckoutController::class, 'checkPromo'])->name('api.checkPromo');

    // ============================
    // PAYMENT GATEWAY (MIDTRANS)
    // ============================
    Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment-status/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment-status/unfinish', [PaymentController::class, 'unfinish'])->name('payment.unfinish');
    Route::get('/payment-status/error', [PaymentController::class, 'error'])->name('payment.error');

    // ============================
    // MIDTRANS WEBHOOK (TIDAK PERLU AUTH)\
    // ============================
    // Route ini harus bisa diakses Midtrans tanpa autentikasi
    Route::post('/midtrans/notification', [PaymentController::class, 'notification'])->name('midtrans.notification');

    // forum 
    Route::get('/forum/create', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');

    Route::patch('/orders/{order}/complete', [DashboardController::class, 'markOrderCompleted'])
        ->name('orders.complete');
    Route::patch('/orders/{order}/cancel', [DashboardController::class, 'cancelOrder'])->name('orders.cancel');


        
});

require __DIR__ . '/auth.php';
