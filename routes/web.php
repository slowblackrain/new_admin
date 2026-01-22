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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', function () {
    return redirect()->route('member.login');
})->name('login');

Route::prefix('goods')->name('goods.')->group(function () {
    Route::get('/view', [GoodsController::class, 'view'])->name('view');
    Route::get('/catalog', [GoodsController::class, 'catalog'])->name('catalog');
    Route::get('/search', [GoodsController::class, 'search'])->name('search');
});

Route::prefix('member')->name('member.')->group(function () {
    Route::get('/login', [MemberController::class, 'login'])->name('login');
    Route::post('/login', [MemberController::class, 'login_process'])->name('login_process');
    Route::get('/logout', [MemberController::class, 'logout'])->name('logout');
    Route::get('/agreement', [MemberController::class, 'agreement'])->name('agreement');
    Route::get('/register', [MemberController::class, 'register'])->name('register');
    Route::post('/register', [MemberController::class, 'register_process'])->name('register_process');
    Route::post('/check_id', [MemberController::class, 'check_id'])->name('check_id');
});

// Cart Routes: Changed prefix to match legacy /order/cart URL structure
Route::prefix('order/cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'store'])->name('store');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/delete', [CartController::class, 'destroy'])->name('destroy');
});

Route::prefix('order')->name('order.')->group(function () {
    Route::post('/order', [OrderController::class, 'index'])->name('form'); // Form submit from Cart
    Route::get('/order', [OrderController::class, 'index'])->name('form_get'); // Optional: if need to support GET with params but POST is better for array
    Route::post('/order/complete', [OrderController::class, 'store'])->name('store');
    Route::get('/order/complete/{id}', [OrderController::class, 'complete'])->name('complete');
});

Route::middleware(['auth'])->prefix('mypage')->name('mypage.')->group(function () {
    Route::get('/delivery-address', [App\Http\Controllers\Front\DeliveryAddressController::class, 'index'])->name('delivery_address.index');
    Route::get('/', [MypageController::class, 'index'])->name('index');
    Route::get('/order/list', [MypageController::class, 'orderList'])->name('order.list');

    // Legacy mapping (optional, but good for redirecting legacy links if needed)
    Route::get('/order_catalog', [MypageController::class, 'orderList'])->name('order.catalog');

    Route::get('/order/view/{id}', [MypageController::class, 'orderView'])->name('order.view');
});

Route::prefix('board')->name('board.')->group(function () {
    Route::get('/', [BoardController::class, 'index'])->name('index');
    Route::get('/view', [BoardController::class, 'view'])->name('view');
});

Route::prefix('service')->name('service.')->group(function () {
    Route::get('/cs', [BoardController::class, 'cs'])->name('cs');
    Route::get('/company', [App\Http\Controllers\Front\ServiceController::class, 'company'])->name('company');
    Route::get('/agreement', [App\Http\Controllers\Front\ServiceController::class, 'agreement'])->name('agreement');
    Route::get('/privacy', [App\Http\Controllers\Front\ServiceController::class, 'privacy'])->name('privacy');
    Route::get('/guide', [App\Http\Controllers\Front\ServiceController::class, 'guide'])->name('guide');
    Route::get('/partnership', [App\Http\Controllers\Front\ServiceController::class, 'partnership'])->name('partnership');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    // Add more admin routes here
});

// Seller Admin Routes
Route::prefix('selleradmin')->name('seller.')->group(function () {
    Route::get('/', [SellerController::class, 'index'])->name('index');
    // Add more seller routes here
});
// Magic Login for Testing
Route::get('/test/login', function () {
    $user = App\Models\Member::first();
    if ($user) {
        Auth::login($user);
        return redirect()->route('home');
    }
    return "No User Found";
    return "No User Found";
})->name('test.login');

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
        
        echo "<h3>Query Log:</h3>";
        dd(DB::getQueryLog());
    } else {
        return "Icon exists for goods_seq " . $goodsSeq . " but product not found??";
    }
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
