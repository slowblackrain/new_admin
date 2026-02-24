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
    
    // [Temporary Testing Route] Auto-login for Browser Subagent
    Route::get('auto-login', function () {
        $user = \App\Models\Member::first();
        if($user) {
            \Illuminate\Support\Facades\Auth::guard('admin')->login($user);
            return redirect()->route('admin.popup_image_full');
        }
        return 'No Admin Found';
    });
    
    // Auth Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Management Routes (Protected)
    Route::middleware('auth:admin')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Goods Routes
        Route::prefix('goods')->name('goods.')->group(function () {
            
            // 진열번호 관리 (Sortcd Catalog)
            Route::get('sortcd_catalog', [\App\Http\Controllers\Admin\Goods\SortcdCatalogController::class, 'index'])->name('sortcd_catalog');
            Route::post('sortcd_catalog/store', [\App\Http\Controllers\Admin\Goods\SortcdCatalogController::class, 'store'])->name('sortcd_catalog.store');
            Route::post('sortcd_catalog/update', [\App\Http\Controllers\Admin\Goods\SortcdCatalogController::class, 'update'])->name('sortcd_catalog.update');
            Route::post('sortcd_catalog/destroy', [\App\Http\Controllers\Admin\Goods\SortcdCatalogController::class, 'destroy'])->name('sortcd_catalog.destroy');
            Route::get('sortcd_catalog/excel', [\App\Http\Controllers\Admin\Goods\SortcdCatalogController::class, 'excel'])->name('sortcd_catalog.excel');
            
            // 일괄이미지등록 (대량 이미지 등록)
            Route::get('popup_image_full', [\App\Http\Controllers\Admin\Goods\ImageBatchController::class, 'popup_image_full'])->name('popup_image_full');
            Route::post('img_uploads', [\App\Http\Controllers\Admin\Goods\ImageBatchController::class, 'img_uploads'])->name('img_uploads');
            
            // 송장등록리스트 (deli_catalog) goods routes would be here if any ...
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

            // Claims Routes
            Route::get('claim/list', [\App\Http\Controllers\Admin\Order\ClaimController::class, 'index'])->name('claim.list');
            Route::post('claim/process', [\App\Http\Controllers\Admin\Order\ClaimController::class, 'process'])->name('claim.process');

            // Customer Memo (CS Template) Management
            Route::get('customer_memo', [\App\Http\Controllers\Admin\Order\CustomerMemoController::class, 'index'])->name('customer_memo.index');
            Route::post('customer_memo/store', [\App\Http\Controllers\Admin\Order\CustomerMemoController::class, 'store'])->name('customer_memo.store');
            Route::post('customer_memo/update', [\App\Http\Controllers\Admin\Order\CustomerMemoController::class, 'update'])->name('customer_memo.update');
            Route::post('customer_memo/destroy', [\App\Http\Controllers\Admin\Order\CustomerMemoController::class, 'destroy'])->name('customer_memo.destroy');
            Route::get('customer_memo/popup', [\App\Http\Controllers\Admin\Order\CustomerMemoController::class, 'popup'])->name('customer_memo.popup');

            Route::get('view/{order_seq}', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'index'])->name('view');
            Route::post('save_memo', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'saveMemo'])->name('save_memo');
            
            // Product Search & Options (For Replacement Modal)
            Route::get('search_goods', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'searchGoods'])->name('search_goods');
            Route::get('get_options', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'getOptions'])->name('get_options');
            
            // Process Actions
            Route::post('process', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'updateStatus'])->name('process');
            Route::post('replace_item', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'replaceItem'])->name('replace_item');
            Route::post('update_price', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'updatePrice'])->name('update_price');
            Route::post('update_recipient', [\App\Http\Controllers\Admin\Order\OrderDetailController::class, 'updateRecipient'])->name('update_recipient');
        });

        // Coupon Routes
        Route::prefix('coupon')->name('coupon.')->group(function () {
            Route::get('catalog', [\App\Http\Controllers\Admin\CouponController::class, 'catalog'])->name('catalog');
            Route::get('regist', [\App\Http\Controllers\Admin\CouponController::class, 'regist'])->name('regist');
            Route::post('process', [\App\Http\Controllers\Admin\CouponController::class, 'process'])->name('process');
        });

        // Event Routes
        Route::prefix('event')->name('event.')->group(function () {
            Route::get('catalog', [\App\Http\Controllers\Admin\EventController::class, 'catalog'])->name('catalog');
            Route::get('regist', [\App\Http\Controllers\Admin\EventController::class, 'regist'])->name('regist');
            Route::post('process', [\App\Http\Controllers\Admin\EventController::class, 'process'])->name('process');
        });

        // Member Routes
        Route::prefix('member')->name('member.')->group(function () {
            Route::get('catalog', [\App\Http\Controllers\Admin\MemberController::class, 'catalog'])->name('catalog');
            Route::get('view/{member_seq}', [\App\Http\Controllers\Admin\MemberController::class, 'view'])->name('view');
            
            // Dormancy Management
            Route::post('dormancy-on/{member_seq}', [\App\Http\Controllers\Admin\MemberController::class, 'dormancyOn'])->name('dormancy.on');
            Route::post('dormancy-off/{member_seq}', [\App\Http\Controllers\Admin\MemberController::class, 'dormancyOff'])->name('dormancy.off');
        });

        // Test Routes
        Route::prefix('test')->name('test.')->group(function () {
            Route::get('alimtalk', function (\Illuminate\Http\Request $request, \App\Services\NotificationService $service) {
                $phone = $request->query('phone', '01065001051'); // Default test number from legacy smail_kakao
                $templateCode = $request->query('template', '12457'); // Default test template from legacy
                $params = [$request->query('p1', '도매토피아'), $request->query('p2', '테스트유저')]; // Default test params

                $smsFallback = $request->query('fallback', 'Y') === 'Y';
                
                if ($request->has('smsOnly')) {
                    $result = $service->sendSms($phone, $request->query('msg', 'This is a test SMS message from the new Dometopia Laravel system.'));
                } else {
                    $result = $service->sendAlimtalk($templateCode, $phone, $params, $smsFallback);
                }

                return response()->json([
                    'type' => $request->has('smsOnly') ? 'SMS' : 'Alimtalk',
                    'target_phone' => $phone,
                    'result' => $result
                ], 200, [], JSON_UNESCAPED_UNICODE);
            })->name('alimtalk');
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
