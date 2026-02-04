<?php
// prepare_e2e_data.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "Preparing E2E Data...\n";
// DB::enableQueryLog();

try {
    DB::delete("DELETE FROM fm_goods WHERE goods_code = '0'");

    $sellerId = 'e2e_' . Str::random(5);
    $seller = Member::create([
        'userid' => $sellerId,
        'user_name' => 'E2E Seller',
        'password' => bcrypt('password'),
        'email' => $sellerId . '@test.com',
        'cellphone' => '010-1234-5678',
        'group_seq' => 2,
        'provider_YN' => 'Y',
        'cash' => 1000000,
        'status' => '1',
        'regist_date' => now(),
    ]);
    DB::table('fm_provider')->insert(['provider_seq' => $seller->member_seq, 'provider_id' => $sellerId, 'provider_name' => 'E2E Corp']);
    echo "1. Seller Created: {$sellerId}\n";

    $buyerId = 'buy_' . Str::random(5);
    $buyer = Member::create([
        'userid' => $buyerId,
        'user_name' => 'E2E Buyer',
        'password' => bcrypt('password'),
        'email' => $buyerId . '@test.com',
        'cellphone' => '010-9876-0000',
        'group_seq' => 1, 
        'status' => '1',
        'regist_date' => now(),
    ]);
    echo "2. Buyer Created: {$buyerId}\n";

    // 3. Prepare ATS Product (Master) - NUMERIC
    $now = date('Y-m-d H:i:s');
    
    // Use Safe Int32 Range (Max 2147483647)
    $masterCode = rand(100000000, 2140000000); 
    
    DB::insert("INSERT INTO fm_goods (goods_name, goods_code, provider_seq, tax, goods_view, regist_date, update_date) VALUES (?, ?, ?, ?, ?, ?, ?)", [
        'E2E Master Product', $masterCode, 1, 'tax', 'look', $now, $now
    ]);
    $sourceGoodsId = DB::getPdo()->lastInsertId();

    DB::table('fm_goods_option')->insert([
        'goods_seq' => $sourceGoodsId,
        'price' => 50, 
        'consumer_price' => 50,
        'provider_price' => 20, 
        'default_option' => 'y'
    ]);
    echo "3. Master Product: {$sourceGoodsId} (Code: {$masterCode})\n";

    // 4. Prepare Seller Product - NUMERIC
    // Ensure unique numeric code
    $sellerCode = rand(100000000, 2140000000);
    if ($sellerCode == $masterCode) $sellerCode++;
    
    DB::insert("INSERT INTO fm_goods (goods_name, goods_code, provider_seq, tax, goods_view, regist_date, update_date) VALUES (?, ?, ?, ?, ?, ?, ?)", [
        'E2E Seller Product', $sellerCode, $seller->member_seq, 'tax', 'look', $now, $now
    ]);
    $sellerGoodsId = DB::getPdo()->lastInsertId();

    DB::table('fm_goods_option')->insert([
        'goods_seq' => $sellerGoodsId,
        'price' => 60,
        'consumer_price' => 60,
        'provider_price' => 20, 
        'default_option' => 'y'
    ]);
    echo "4. Seller Product: {$sellerGoodsId} (Code: {$sellerCode})\n";

    $json = json_encode([
        'seller_seq' => $seller->member_seq,
        'buyer_seq' => $buyer->member_seq,
        'seller_goods_seq' => $sellerGoodsId,
        'master_goods_seq' => $sourceGoodsId,
    ]);
    
    $path = 'C:\dometopia\new_admin\e2e_env.json';
    $bytes = file_put_contents($path, $json);
    
    if ($bytes === false) {
        throw new \Exception("File Write Failed to $path");
    }
    echo "Done. Saved to $path\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
