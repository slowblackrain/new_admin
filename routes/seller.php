<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Seller\Auth\LoginController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ATSController;

/*
|--------------------------------------------------------------------------
| Seller Admin Routes
|--------------------------------------------------------------------------
|
| Prefix: /selleradmin
| Name Prefix: seller.
| Guard: seller
|
*/

Route::prefix('selleradmin')->name('seller.')->group(function () {
    
    // Auth Routes
    Route::middleware('guest:seller')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware('auth:seller')->group(function () {
        Route::get('/', function () {
            return redirect()->route('seller.dashboard');
        });
        
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Goods (Product) Routes
        Route::prefix('goods')->name('goods.')->group(function () {
            Route::get('regist', [ProductController::class, 'create'])->name('create');
            Route::post('regist', [ProductController::class, 'store'])->name('store');
        });

        // ATS (Product Investment) Routes
        Route::prefix('ats')->name('ats.')->group(function () {
            Route::get('catalog', [ATSController::class, 'catalog'])->name('catalog');
            Route::get('social_catalog', [ATSController::class, 'social_catalog'])->name('social_catalog');
            Route::get('settlement', [ATSController::class, 'settlement'])->name('settlement');
            Route::post('runout', [ATSController::class, 'requestRunout'])->name('runout');

        });

        // Linked Order (OrderPlayauto) Routes
        Route::prefix('order_playauto')->name('order.')->group(function () {
            Route::get('catalog', [\App\Http\Controllers\Seller\OrderPlayautoController::class, 'catalog'])->name('catalog');
        });

        // Export (Order Fulfillment) Routes
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('catalog', [\App\Http\Controllers\Seller\SellerExportController::class, 'catalog'])->name('catalog');
            Route::get('view/{id}', [\App\Http\Controllers\Seller\SellerExportController::class, 'view'])->name('view');
        });

        // Point & Cash Routes
        Route::prefix('point')->name('point.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\PointController::class, 'index'])->name('index');
            Route::get('emoney', [\App\Http\Controllers\Seller\PointController::class, 'emoney'])->name('emoney');
            Route::get('cash', [\App\Http\Controllers\Seller\PointController::class, 'cash'])->name('cash');
        });

        // Statistics Routes
        Route::prefix('statistics')->name('statistics.')->group(function () {
            Route::get('goods', [\App\Http\Controllers\Seller\StatisticController::class, 'index'])->name('goods');
        });

        // Board Routes
        Route::prefix('board')->name('board.')->group(function () {
            Route::get('{id}/write', [\App\Http\Controllers\Seller\BoardController::class, 'create'])->name('create');
            Route::post('{id}/store', [\App\Http\Controllers\Seller\BoardController::class, 'store'])->name('store');
            Route::get('{id}/view/{seq}', [\App\Http\Controllers\Seller\BoardController::class, 'show'])->name('show');
            Route::get('{id}', [\App\Http\Controllers\Seller\BoardController::class, 'index'])->name('index');
        });
    });

    // Test Login Route (For Verification)
    Route::get('test/login', function () {
        $providerId = 'newjjang3'; // Updated test provider as per request
        $seller = \App\Models\Seller::where('provider_id', $providerId)->first();
        if ($seller) {
            \Illuminate\Support\Facades\Auth::guard('seller')->login($seller);
            return redirect()->route('seller.dashboard');
        }
        return "Provider not found";
    });
});
