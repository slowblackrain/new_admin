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

        // Goods Routes
        Route::prefix('goods')->name('goods.')->group(function () {
             // ... existing goods routes would be here if any ...
             Route::get('catalog', [\App\Http\Controllers\Admin\GoodsController::class, 'catalog'])->name('catalog');
             Route::get('regist', [\App\Http\Controllers\Admin\GoodsController::class, 'create'])->name('regist');
             Route::post('regist', [\App\Http\Controllers\Admin\GoodsController::class, 'store'])->name('store');
             
             // Brand Routes
             Route::prefix('brand')->name('brand.')->group(function () {
                 Route::get('/', [\App\Http\Controllers\Admin\Goods\BrandController::class, 'index'])->name('index');
                 Route::any('tree', [\App\Http\Controllers\Admin\Goods\BrandController::class, 'tree'])->name('tree'); // Uses POST/GET
                 Route::get('show/{code}', [\App\Http\Controllers\Admin\Goods\BrandController::class, 'show'])->name('show');
                 Route::put('update/{id}', [\App\Http\Controllers\Admin\Goods\BrandController::class, 'update'])->name('update');
             });

             // Batch & Excel Routes
             Route::prefix('batch')->name('batch.')->group(function () {
                 Route::post('modify', [\App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'batch_modify'])->name('modify');
                 Route::get('search_json', [\App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'search_json'])->name('search_json'); // JSON API
                 Route::get('modify', function(\Illuminate\Http\Request $request) {
                     $keyword = $request->input('keyword', '');
                     $goods = \App\Models\Goods::when($keyword, function($q) use ($keyword){
                        $q->where('goods_name', 'like', "%{$keyword}%")
                          ->orWhere('goods_code', 'like', "%{$keyword}%");
                     })->paginate(20);
                     return view('admin.goods.batch_modify', compact('goods', 'keyword'));
                 })->name('modify_view');
                 Route::get('excel_form', [\App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'excel_form'])->name('excel_form'); // Upload UI
                 Route::post('excel_upload', [\App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'excel_upload'])->name('excel_upload');
                 Route::get('excel_download', [\App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'excel_download'])->name('excel_download');
             });
        });

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

    // SCM Routes (Protected)
    Route::middleware('auth:admin')->group(function () {
        Route::prefix('scm')->name('scm_')->group(function () {
            // Basic Config
            Route::prefix('basic')->name('basic.')->group(function () {
                Route::get('config', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'config'])->name('config');
                Route::post('config', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'save_config'])->name('save_config');

                Route::get('goods_int_set', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'goods_int_set'])->name('goods_int_set');
                Route::post('goods_int_set', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'save_goods_int_set'])->name('save_goods_int_set');

                Route::get('warehouse', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_list'])->name('warehouse');
                Route::get('warehouse/form', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_form'])->name('warehouse.form');
                Route::post('warehouse/save', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_save'])->name('warehouse.save');

                Route::get('store', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_list'])->name('store');
                Route::get('store/form', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_form'])->name('store.form');
                Route::post('store/save', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_save'])->name('store.save');
                
                Route::get('trader', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_list'])->name('trader');
                Route::get('trader/form', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_form'])->name('trader.form');
                Route::post('trader/save', [\App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_save'])->name('trader.save');
            });

            // Management
            Route::prefix('manage')->name('manage.')->group(function() {
                // Revision
                Route::get('revision', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'revision'])->name('revision');
                Route::get('revision/regist', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'revision_regist'])->name('revision.regist');
                Route::post('revision/save', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'revision_save'])->name('revision.save');

                // Stock Move
                Route::get('stockmove', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove'])->name('stockmove');
                Route::get('stockmove/regist', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove_regist'])->name('stockmove.regist');
                Route::post('stockmove/save', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove_save'])->name('stockmove.save');

                // Ledger
                Route::get('ledger', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'ledger'])->name('ledger');
                
                // Inventory Asset Report
                Route::get('inven', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'inven'])->name('inven');
                
                // Inventory In/Out History (Period Summary)
                Route::get('inout_catalog', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'inout_catalog'])->name('inout_catalog');
                
                // SCM Goods List
                Route::get('goods', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'goods'])->name('goods');

                // Ledger Detail Logic
                Route::get('ledger_detail', [\App\Http\Controllers\Admin\Scm\ScmManageController::class, 'ledger_detail'])->name('ledger_detail');
            });

            // Order
            Route::prefix('order')->name('order.')->group(function() {
                Route::get('list', [\App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'index'])->name('list');
                Route::post('auto-order', [\App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'storeAutoOrder'])->name('store_auto_order');
                Route::post('confirm', [\App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'confirm'])->name('confirm');
                Route::post('receive', [\App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'receive'])->name('receive');
                Route::post('carryingout', [\App\Http\Controllers\Admin\Scm\ScmCarryingOutController::class, 'store'])->name('store_carryingout');
                Route::post('revision', [\App\Http\Controllers\Admin\Scm\ScmRevisionController::class, 'store'])->name('store_revision');
            });
        });
    });
});
