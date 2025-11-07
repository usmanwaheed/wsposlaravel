<?php

use App\Http\Controllers\API\InventorySyncController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\WooCommerceSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/sync', [WooCommerceSyncController::class, 'syncProduct']);

    Route::post('/inventory/update', [InventorySyncController::class, 'update']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/import', [OrderController::class, 'importFromWooCommerce']);

    Route::get('/reports/sales/daily', [ReportController::class, 'dailySales']);
    Route::get('/reports/sales/monthly', [ReportController::class, 'monthlySales']);
    Route::get('/reports/products', [ReportController::class, 'productPerformance']);
    Route::get('/reports/categories', [ReportController::class, 'categoryPerformance']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('/reports/payments', [ReportController::class, 'paymentSummary']);

    Route::post('/woocommerce/sync/products', [WooCommerceSyncController::class, 'syncProducts']);
    Route::post('/woocommerce/sync/inventory', [WooCommerceSyncController::class, 'syncInventory']);
    Route::post('/woocommerce/sync/orders', [WooCommerceSyncController::class, 'syncOrders']);
});
