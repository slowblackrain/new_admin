<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\GoodsController;
use App\Http\Controllers\Front\MemberController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\MypageController;
use App\Http\Controllers\Front\BoardController;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;
require __DIR__.'/seller.php';
require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test/force-login/{userid}', function ($userid) {
    if (!app()->isLocal()) abort(404);
    $user = \App\Models\Member::where('userid', $userid)->firstOrFail();
    \Illuminate\Support\Facades\Auth::login($user);
    return redirect()->route('home');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', function () {
    return redirect()->route('member.login');
})->name('login');

// Front & Member Routes
Route::prefix('common')->name('common.')->group(function () {
    Route::get('/get_right_display', [App\Http\Controllers\Front\CommonController::class, 'getRightDisplay'])->name('get_right_display');
    Route::get('/get_right_total', [App\Http\Controllers\Front\CommonController::class, 'getRightTotal'])->name('get_right_total');
});
Route::post('/goods/goods_recent_del', [App\Http\Controllers\Front\CommonController::class, 'deleteRecentItem'])->name('goods.recent_del');
Route::get('/popup/designpopup', [App\Http\Controllers\Front\PopupController::class, 'show'])->name('popup.show');

Route::prefix('goods')->name('goods.')->group(function () {
    Route::get('/view', [GoodsController::class, 'view'])->name('view');
    Route::get('/catalog', [GoodsController::class, 'catalog'])->name('catalog');
    Route::get('/search', [GoodsController::class, 'search'])->name('search');
    Route::get('/restock/register', [App\Http\Controllers\Front\RestockController::class, 'register'])->name('restock.register');
    Route::post('/restock/store', [App\Http\Controllers\Front\RestockController::class, 'store'])->name('restock.store');
});

Route::prefix('member')->name('member.')->group(function () {
    Route::get('/login', [MemberController::class, 'login'])->name('login');
    Route::post('/login', [MemberController::class, 'login_process'])->name('login_process');
    Route::get('/logout', [MemberController::class, 'logout'])->name('logout');
    Route::get('/agreement', [MemberController::class, 'agreement'])->name('agreement');
    Route::get('/register', [MemberController::class, 'register'])->name('register');
    Route::post('/register', [MemberController::class, 'register_process'])->name('register_process');
    Route::post('/check_id', [MemberController::class, 'check_id'])->name('check_id');

    // ID/PW Find
    Route::get('/find_id', [MemberController::class, 'find_id'])->name('find_id');
    Route::post('/find_id', [MemberController::class, 'find_id_result'])->name('find_id_result');

    Route::get('/find_pw', [MemberController::class, 'find_pw'])->name('find_pw');
    Route::post('/find_pw', [MemberController::class, 'find_pw_result'])->name('find_pw_result');
});

Route::prefix('order/cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'store'])->name('store');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/delete', [CartController::class, 'destroy'])->name('destroy');
});

Route::prefix('order')->name('order.')->group(function () {
    Route::post('/form', [OrderController::class, 'index'])->name('form');
    Route::get('/form', [OrderController::class, 'index'])->name('form_get');
    Route::post('/pay', [OrderController::class, 'store'])->name('store');
    Route::get('/complete/{id}', [OrderController::class, 'complete'])->name('complete');
});

Route::middleware(['auth'])->prefix('mypage')->name('mypage.')->group(function () {
    // Member Info Modification
    Route::get('/my-info/check', [App\Http\Controllers\Front\MemberModifyController::class, 'checkPassword'])->name('member.check_password');
    Route::post('/my-info/check', [App\Http\Controllers\Front\MemberModifyController::class, 'verifyPassword'])->name('member.verify_password');
    Route::get('/my-info/edit', [App\Http\Controllers\Front\MemberModifyController::class, 'edit'])->name('member.edit');
    Route::put('/my-info/update', [App\Http\Controllers\Front\MemberModifyController::class, 'update'])->name('member.update');

    // Member Withdrawal
    Route::get('/drop', [App\Http\Controllers\Front\MemberDropController::class, 'index'])->name('member.drop');
    Route::post('/drop', [App\Http\Controllers\Front\MemberDropController::class, 'leave'])->name('member.leave');

    Route::prefix('delivery-address')->name('delivery_address.')->group(function() {
        Route::get('/', [App\Http\Controllers\Front\DeliveryAddressController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Front\DeliveryAddressController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Front\DeliveryAddressController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Front\DeliveryAddressController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Front\DeliveryAddressController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Front\DeliveryAddressController::class, 'destroy'])->name('destroy');
        Route::get('/json', [App\Http\Controllers\Front\DeliveryAddressController::class, 'listJson'])->name('json');
    });
    Route::get('/', [MypageController::class, 'index'])->name('index');
    Route::get('/order/list', [MypageController::class, 'orderList'])->name('order.list');
    Route::get('/order/claim', [MypageController::class, 'orderClaimList'])->name('order.claim_list');
    
    // Wishlist
    Route::get('/wishlist', [MypageController::class, 'wishlist'])->name('wishlist');
    Route::delete('/wishlist/{id}', [MypageController::class, 'wishlistDestroy'])->name('wishlist.destroy');

    Route::post('/order/add-item', [\App\Http\Controllers\Admin\Order\OrderProcessController::class, 'addItem'])->name('order.addItem');
    Route::get('/order_catalog', [MypageController::class, 'orderList'])->name('order.catalog');
    Route::get('/order/view/{id}', [MypageController::class, 'orderView'])->name('order.view');
    Route::post('/order/confirm/{orderSeq}', [MypageController::class, 'confirmPurchase'])->name('order.confirm');

    // Claim Routes
    Route::get('/claim/apply/{orderSeq}/{type}', [MypageController::class, 'claimApply'])->name('claim.apply');
    Route::post('/claim/store/{orderSeq}', [MypageController::class, 'claimStore'])->name('claim.store');

    // Benefit Routes (Coupon, Emoney, Point)
    Route::get('/coupon', [MypageController::class, 'couponList'])->name('coupon');
    Route::get('/emoney', [MypageController::class, 'emoneyList'])->name('emoney');
    Route::get('/point', [MypageController::class, 'pointList'])->name('point');
});

Route::prefix('board')->name('board.')->group(function () {
    Route::get('/', [BoardController::class, 'index'])->name('index');
    Route::get('/view', [BoardController::class, 'view'])->name('view');
    
    // Write routes (protected by auth middleware usually, but controller checks auth too)
    Route::middleware(['auth'])->group(function() {
        Route::get('/write', [BoardController::class, 'create'])->name('write');
        Route::post('/write', [BoardController::class, 'store'])->name('store');
        Route::post('/comment', [BoardController::class, 'commentStore'])->name('comment.store');
    });

    // Public partial view for goods
    Route::get('/goods-list', [BoardController::class, 'getGoodsBoardList'])->name('goods.list');
});

Route::prefix('service')->name('service.')->group(function () {
    Route::get('/cs', [BoardController::class, 'cs'])->name('cs');
    Route::get('/company', [App\Http\Controllers\Front\ServiceController::class, 'company'])->name('company');
    Route::get('/agreement', [App\Http\Controllers\Front\ServiceController::class, 'agreement'])->name('agreement');
    Route::get('/privacy', [App\Http\Controllers\Front\ServiceController::class, 'privacy'])->name('privacy');
    Route::get('/guide', [App\Http\Controllers\Front\ServiceController::class, 'guide'])->name('guide');
    Route::get('/partnership', [App\Http\Controllers\Front\ServiceController::class, 'partnership'])->name('partnership');
});

Route::prefix('promotion')->name('promotion.')->group(function () {
    Route::get('/', [App\Http\Controllers\Front\PromotionController::class, 'index'])->name('index');
    Route::get('/view', [App\Http\Controllers\Front\PromotionController::class, 'view'])->name('view');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // Goods Routes
    Route::prefix('goods')->name('goods.')->group(function () {
        Route::post('search', [App\Http\Controllers\Admin\GoodsController::class, 'search'])->name('search');
        Route::post('options', [App\Http\Controllers\Admin\GoodsController::class, 'getOptions'])->name('options');
        
        // Management
        Route::get('regist', [App\Http\Controllers\Admin\GoodsController::class, 'create'])->name('regist');
        Route::post('regist', [App\Http\Controllers\Admin\GoodsController::class, 'store'])->name('store');
        Route::get('catalog', [App\Http\Controllers\Admin\GoodsController::class, 'catalog'])->name('catalog');
        Route::get('edit/{id}', [App\Http\Controllers\Admin\GoodsController::class, 'edit'])->name('edit');
        Route::post('update_options', [App\Http\Controllers\Admin\GoodsController::class, 'updateOptions'])->name('update_options');
        Route::post('calculate_price', [App\Http\Controllers\Admin\GoodsController::class, 'calculatePrice'])->name('calculate_price');
        Route::post('category_children', [App\Http\Controllers\Admin\GoodsController::class, 'getCategoryChildren'])->name('category_children');

        // Bulk Operations
        Route::get('batch_modify', [App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'batch_modify'])->name('batch_modify');
        Route::post('batch_save', [App\Http\Controllers\Admin\Goods\GoodsBatchController::class, 'save_batch'])->name('batch_save');
    });

    // SCM Basic
    Route::prefix('scm_basic')->name('scm_basic.')->group(function () {
        Route::get('config', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'config'])->name('config');
        Route::post('config', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'save_config'])->name('save_config');

        Route::get('goods_int_set', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'goods_int_set'])->name('goods_int_set');
        Route::post('goods_int_set', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'save_goods_int_set'])->name('save_goods_int_set');

        Route::get('warehouse', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_list'])->name('warehouse');
        Route::get('warehouse/regist', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_form'])->name('warehouse.form');
        Route::post('warehouse/save', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'warehouse_save'])->name('warehouse.save');

        Route::get('store', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_list'])->name('store');
        Route::get('store/regist', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_form'])->name('store.form');
        Route::post('store/save', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'store_save'])->name('store.save');

        Route::get('trader', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_list'])->name('trader');
        Route::get('trader/regist', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_form'])->name('trader.form');
        Route::post('trader/save', [App\Http\Controllers\Admin\Scm\ScmBasicController::class, 'trader_save'])->name('trader.save');
    });

    // SCM Order (Balju)
    Route::prefix('scm_order')->name('scm_order.')->group(function () {
        Route::get('auto', [App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'auto_order'])->name('auto');
        Route::get('create', [App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'create_auto_order'])->name('create');
        Route::post('create', [App\Http\Controllers\Admin\Scm\ScmOrderController::class, 'create_auto_order'])->name('create');
        Route::get('list', [App\Http\Controllers\Admin\Scm\ScmOfferController::class, 'index'])->name('list');
        Route::get('excel', [App\Http\Controllers\Admin\Scm\ScmOfferController::class, 'excel'])->name('excel'); // Added Route
        Route::post('update_status', [App\Http\Controllers\Admin\Scm\ScmOfferController::class, 'update_status'])->name('update_status');
        Route::post('update_field', [App\Http\Controllers\Admin\Scm\ScmOfferController::class, 'updateField'])->name('update_field');
        Route::get('detail', [App\Http\Controllers\Admin\Scm\ScmOfferController::class, 'detail'])->name('detail');
        
        // Failure Log (Super Admin)
        Route::get('fail_log', [App\Http\Controllers\Admin\Scm\ScmOrderFailController::class, 'index'])->name('fail_log');
    });

    // SCM Manage
    Route::prefix('scm_manage')->name('scm_manage.')->group(function () {
        Route::get('revision', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'revision'])->name('revision');
        Route::post('save_revision', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'save_revision'])->name('save_revision');
        Route::get('ledger', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'ledger'])->name('ledger');

        // Stock Move
        Route::get('stockmove', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove'])->name('stockmove');
        Route::get('stockmove/regist', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove_regist'])->name('stockmove.regist');
        Route::post('stockmove/save', [App\Http\Controllers\Admin\Scm\ScmManageController::class, 'stockmove_save'])->name('stockmove.save');
    });

    // SCM Settlement
    Route::prefix('scm_settlement')->name('scm_settlement.')->group(function () {
        Route::get('index', [App\Http\Controllers\Admin\Scm\ScmSettlementController::class, 'index'])->name('index');
        Route::put('{seq}', [App\Http\Controllers\Admin\Scm\ScmSettlementController::class, 'update'])->name('update');
    });

    // Returns (반품)
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('catalog', [App\Http\Controllers\Admin\ReturnsController::class, 'catalog'])->name('catalog');
    });

    // Refund (환불)
    Route::prefix('refund')->name('refund.')->group(function () {
        Route::get('catalog', [App\Http\Controllers\Admin\RefundController::class, 'catalog'])->name('catalog');
    });

    // [설정] setting
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('index', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
        Route::get('basic', [App\Http\Controllers\Admin\SettingController::class, 'basic'])->name('basic');
        Route::post('basic', [App\Http\Controllers\Admin\SettingController::class, 'save_basic'])->name('basic.save');
        Route::get('operating', [App\Http\Controllers\Admin\SettingController::class, 'operating'])->name('operating');
        Route::get('pg', [App\Http\Controllers\Admin\SettingController::class, 'pg'])->name('pg');
        Route::get('member', [App\Http\Controllers\Admin\SettingController::class, 'member'])->name('member');
        Route::get('protect', [App\Http\Controllers\Admin\SettingController::class, 'protect'])->name('protect');
    });

    // [통계] statistic
    Route::get('statistic_summary', [App\Http\Controllers\Admin\StatisticSummaryController::class, 'index'])->name('statistic_summary.index');
    Route::get('statistic_visitor', [App\Http\Controllers\Admin\StatisticSummaryController::class, 'visitor'])->name('statistic_visitor.index');
});

// Front Payment Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/request', [App\Http\Controllers\Front\PaymentController::class, 'request'])->name('request');
    Route::any('/success', [App\Http\Controllers\Front\PaymentController::class, 'success'])->name('success');
    Route::any('/fail', [App\Http\Controllers\Front\PaymentController::class, 'fail'])->name('fail');
    Route::any('/pairing/receive', [App\Http\Controllers\Front\PaymentController::class, 'pairingReceive'])->name('pairing.receive');
});

/*
// Legacy Debug Routes
Route::get('/test/login', function () {
    $user = App\Models\Member::first();
    if ($user) {
        Auth::login($user);
        return redirect()->route('home');
    }
    return "No User Found";
})->name('test.login');
*/

// Debug Route for Icons
Route::get('/test-icons', function () {
    DB::enableQueryLog();
    
    // Check if ANY icons exist
    $anyIcon = \App\Models\GoodsIcon::first();
    if (!$anyIcon) {
        return "TABLE fm_goods_icon IS EMPTY. No icons to display.";
    }
    
    // Use the goods_seq from the existing icon
    $goodsSeq = $anyIcon->goods_seq;
    $goods = Goods::with('activeIcons')->find($goodsSeq);
    
    if ($goods) {
        echo "<h1>Found Product with Icon: " . $goods->goods_seq . " (" . $goods->goods_name . ")</h1>";
        echo "<h3>Icons Count: " . $goods->activeIcons->count() . "</h3>";
        
        foreach($goods->activeIcons as $icon) {
            echo "Icon: " . $icon->codecd . " | Start: " . $icon->start_date . " | End: " . $icon->end_date . "<br>";
        }
        
    }
});


// Debug Schema
Route::get('/debug/schema/{table}', function ($table) {
    if (!Schema::hasTable($table)) return "Table not found";
    return response()->json(Schema::getColumnListing($table));
});

Route::get('/debug/table/{table}', function ($table) {
    if (!Schema::hasTable($table)) return "Table not found";
    return response()->json(DB::table($table)->orderByDesc(Schema::hasColumn($table, 'sno') ? 'sno' : (Schema::hasColumn($table, 'seq') ? 'seq' : 'regist_date'))->limit(5)->get());
});

Route::get('/debug/describe/{table}', function ($table) {
    return response()->json(DB::select("DESCRIBE $table"));
});

Route::get('/debug/index/{table}', function ($table) {
    return response()->json(DB::select("SHOW INDEX FROM $table"));
});




// Legacy AJAX Route for Category Menu
Route::any('/main/category_search_initial', function (\Illuminate\Http\Request $request) {
    $code = $request->input('code', 'all');
    $depth = $request->input('depth', 1);

    // Start building query
    $query = \App\Models\Category::where('level', $depth)->where('hide', '0');

    // Filter by Initial Consonant (Legacy Logic)
    if ($code !== 'all') {
        $hangulRanges = [
            'ga' => '^[가-깋]', // ㄱ
            'na' => '^[나-닣]', // ㄴ
            'da' => '^[다-딯]', // ㄷ
            'ra' => '^[라-맇]', // ㄹ
            'ma' => '^[마-밓]', // ㅁ
            'ba' => '^[바-빟]', // ㅂ
            'sa' => '^[사-싷]', // ㅅ
            'aa' => '^[아-잏]', // ㅇ
            'za' => '^[자-쮷]', // ㅈ
            'cha' => '^[차-칳]', // ㅊ
            'ka' => '^[카-킿]', // ㅋ
            'ta' => '^[타-팋]', // ㅌ
            'pa' => '^[파-핗]', // ㅍ
            'ha' => '^[하-힣]'  // ㅎ
        ];

        if (array_key_exists($code, $hangulRanges)) {
            $query->where('title', 'REGEXP', $hangulRanges[$code]);
        }
    }

    // Legacy sorting often uses 'position' or similar. 
    $categories = $query->orderBy('position')->get();

    if ($categories->isEmpty()) {
        return "no_category";
    }

    return view('front.main.category_list', ['categories' => $categories]);
})->name('main.category_initial');

