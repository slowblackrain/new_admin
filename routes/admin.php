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
        Route::get('bank_check', [\App\Http\Controllers\Admin\OrderController::class, 'bank_check'])->name('bank_check');
        Route::post('update_status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('update_status');
        Route::get('view/{order_seq}', [\App\Http\Controllers\Admin\OrderController::class, 'view'])->name('view');
    });

    // Member Routes
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\MemberController::class, 'catalog'])->name('catalog');
        Route::get('view/{member_seq}', [\App\Http\Controllers\Admin\MemberController::class, 'view'])->name('view');
    });

    // Provider Routes
    Route::prefix('provider')->name('provider.')->group(function () {
        Route::get('catalog', [\App\Http\Controllers\Admin\ProviderController::class, 'catalog'])->name('catalog');
    });
});
