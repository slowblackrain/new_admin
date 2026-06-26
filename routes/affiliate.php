<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Affiliate\AffiliateAuthController;
use App\Http\Controllers\Affiliate\DashboardController;
use App\Http\Controllers\Affiliate\AffiliateSettingController;

Route::prefix('affiliate')->name('affiliate.')->group(function () {
    
    // Auth Routes
    Route::get('login', [AffiliateAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AffiliateAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AffiliateAuthController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Settings
        Route::get('settings', [AffiliateSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [AffiliateSettingController::class, 'store'])->name('settings.store');
        
        // Category Mappings
        Route::get('/settings/category', [AffiliateSettingController::class, 'categoryMapping'])->name('settings.category');
        Route::post('/settings/category', [AffiliateSettingController::class, 'storeCategoryMapping'])->name('settings.category.store');
        Route::post('/settings/category/auto-map', [AffiliateSettingController::class, 'autoMapCategories'])->name('settings.category.auto');
        
        // Goods Sync (Batch)
        Route::get('/settings/sync', [AffiliateSettingController::class, 'syncIndex'])->name('settings.sync');
        Route::post('/settings/sync/chunk', [AffiliateSettingController::class, 'syncChunk'])->name('settings.sync.chunk');
    });

});
