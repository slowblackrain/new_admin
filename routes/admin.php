<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Prefix: /admin
| Name Prefix: admin.
| Guard: admin
|
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Auth Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        
        
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Test Login Route (For Verification - Remove in Production)
    Route::get('test/login', function () {
        $admin = \App\Models\Admin::where('manager_id', 'dmtadmin')->first();
        if ($admin) {
            \Illuminate\Support\Facades\Auth::guard('admin')->login($admin);
            return redirect()->route('admin.dashboard');
        }
        return "Admin user 'dmtadmin' not found.";
    });

    // Order Routes
    Route::prefix('order')->name('order.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\OrderController::class, 'catalog'])->name('catalog');
        
        // Bank Check Routes
        Route::get('bank_check', [\App\Http\Controllers\Admin\Order\BankCheckController::class, 'index'])->name('bank_check');
        Route::get('bank_check/match', [\App\Http\Controllers\Admin\Order\BankCheckController::class, 'matchCandidates'])->name('bank_check.match');
        Route::post('bank_check/process', [\App\Http\Controllers\Admin\Order\BankCheckController::class, 'processMatch'])->name('bank_check.process');

        Route::get('view/{order_seq}', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'index'])->name('view');
        
        // Product Search & Options (For Replacement Modal)
        Route::get('search_goods', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'searchGoods'])->name('search_goods');
        Route::get('get_options', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'getOptions'])->name('get_options');
        
        // Process Actions
        Route::post('process', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'updateStatus'])->name('process');
        Route::post('replace_item', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'replaceItem'])->name('replace_item');
        Route::post('update_price', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'updatePrice'])->name('update_price');
        Route::post('update_recipient', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'updateRecipient'])->name('update_recipient');
    });

    // Member Routes
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\MemberController::class, 'catalog'])->name('catalog');
        Route::get('view/{member_seq}', [\App\Http\Controllers\Admin\MemberController::class, 'view'])->name('view');
    });

    // Category Routes
    Route::prefix('category')->name('category.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\CategoryController::class, 'catalog'])->name('catalog');
        Route::get('tree', [\App\Http\Controllers\Admin\CategoryController::class, 'getTree'])->name('tree');
        Route::get('detail/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'getDetail'])->name('detail');
        Route::get('goods/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'getGoods'])->name('goods');
        Route::post('store', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
        Route::post('update/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
        Route::post('move', [\App\Http\Controllers\Admin\CategoryController::class, 'move'])->name('move');
        Route::post('destroy/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
    });

    // Provider Routes
    Route::prefix('provider')->name('provider.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\ProviderController::class, 'catalog'])->name('catalog');
    });
});
