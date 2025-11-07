<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\ProductManagementController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/pos', 'pos.index')->name('pos.index');

    Route::middleware('can:manage-products')->group(function () {
        Route::get('products/{product}/barcodes', [ProductManagementController::class, 'barcodes'])->name('products.barcodes');
        Route::resource('products', ProductManagementController::class)->except(['show']);

        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::patch('inventory/{variation}', [InventoryController::class, 'update'])->name('inventory.update');
    });

    Route::middleware('can:view-reports')->group(function () {
        Route::get('orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/invoice', [OrderManagementController::class, 'invoice'])->name('orders.invoice');
    });
});
