<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PraKonsulController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', [LandingController::class, 'index'])->name('landing');

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
    // SHOP
    // ============================
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

    // ============================
    // CART
    // ============================
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
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
    // Halaman Form Booking
    Route::get('/appointment/buat', [AppointmentController::class, 'create'])->name('appointment.create');
    // Proses Simpan Booking
    Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment.store');
    // list appointment pasien
    Route::get('/appointment', [AppointmentController::class, 'index'])
        ->name('appointment.index');
});

require __DIR__ . '/auth.php';
