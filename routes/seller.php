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
| Prefix: /seller
| Name Prefix: seller.
| Guard: seller
|
*/

Route::prefix('seller')->name('seller.')->group(function () {
    
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

        // My Info
        Route::prefix('my')->name('my.')->group(function () {
            Route::get('info', [\App\Http\Controllers\Seller\SellerInfoController::class, 'index'])->name('index');
            Route::post('info', [\App\Http\Controllers\Seller\SellerInfoController::class, 'update'])->name('update');
        });

        // Settlement (Account)
        Route::prefix('account')->name('account.')->group(function () {
             Route::get('list', [\App\Http\Controllers\Seller\SellerAccountController::class, 'index'])->name('index');
        });

        // Goods (Product) Routes
        Route::prefix('goods')->name('goods.')->group(function () {
            Route::get('catalog', [ProductController::class, 'index'])->name('index'); // catalog or index? Sidebar usually uses 'catalog' term for lists. Let's use 'index' as name but 'catalog' as URL to match others? Or just 'list'.
            // task said 'seller.goods.index'.
            Route::get('list', [ProductController::class, 'index'])->name('index'); 
            Route::get('regist', [ProductController::class, 'create'])->name('create');
            Route::post('regist', [ProductController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ProductController::class, 'edit'])->name('edit');
            Route::post('edit/{id}', [ProductController::class, 'update'])->name('update'); // Using POST for update to avoid MethodNotAllowed if standard HTML form (though we can use @method('PUT')). Let's stick to POST or add @method('PUT') in form.
            // I'll use POST in route for simplicity unless I put @method('PUT') in form. I didn't put @method('PUT') in form above.
            // So Route::post is correct.
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
            Route::get('excel_upload', [\App\Http\Controllers\Seller\OrderPlayautoController::class, 'excelupload'])->name('excel_upload');
            Route::post('excel_upload', [\App\Http\Controllers\Seller\OrderPlayautoController::class, 'excelupload_process'])->name('excel_upload_process');
            Route::post('excel_store', [\App\Http\Controllers\Seller\OrderPlayautoController::class, 'excelupload_store'])->name('excel_store');
        });

        // General Order Management (SellerOrderController)
        Route::prefix('order')->name('order_mgmt.')->group(function () {
             Route::get('catalog', [\App\Http\Controllers\Seller\SellerOrderController::class, 'index'])->name('catalog');
             Route::get('view/{id}', [\App\Http\Controllers\Seller\SellerOrderController::class, 'show'])->name('view');
        });

    // Return & Refund
    // Return & Refund
    Route::get('/return', [App\Http\Controllers\Seller\SellerReturnController::class, 'index'])->name('return.index');
    Route::get('/return/{id}', [App\Http\Controllers\Seller\SellerReturnController::class, 'show'])->name('return.show');
    
    Route::get('/refund', [App\Http\Controllers\Seller\SellerRefundController::class, 'index'])->name('refund.index');
    Route::get('/refund/{id}', [App\Http\Controllers\Seller\SellerRefundController::class, 'show'])->name('refund.show');

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
            Route::get('/', [\App\Http\Controllers\Seller\SellerStatisticController::class, 'index'])->name('index');
            Route::get('/sales_monthly', [\App\Http\Controllers\Seller\SellerStatisticController::class, 'sales_monthly'])->name('sales_monthly');
            Route::get('/sales_daily', [\App\Http\Controllers\Seller\SellerStatisticController::class, 'sales_daily'])->name('sales_daily');
            Route::get('/goods', [\App\Http\Controllers\Seller\SellerStatisticController::class, 'goods'])->name('goods');
        });

        // Board Routes
        Route::prefix('board')->name('board.')->group(function () {
            Route::get('{id}/write', [\App\Http\Controllers\Seller\BoardController::class, 'create'])->name('create');
            Route::post('{id}/store', [\App\Http\Controllers\Seller\BoardController::class, 'store'])->name('store');
            Route::get('{id}/view/{seq}', [\App\Http\Controllers\Seller\BoardController::class, 'show'])->name('show');
            Route::get('{id}', [\App\Http\Controllers\Seller\BoardController::class, 'index'])->name('index');
        })->where('id', '[a-zA-Z0-9_]+');
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
