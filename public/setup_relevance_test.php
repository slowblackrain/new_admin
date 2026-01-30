<?php
use App\Models\Goods;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Target Products (Arbitrary IDs that exist)
$pNameDetails = Goods::find(206718); // Existing '프라임 골드 볼펜...'
$pKeywordDetails = Goods::find(1898);   // Existing '똑딱이 손난로...'

if (!$pNameDetails || !$pKeywordDetails) {
    die("Products not found.");
}

// Setup
$pNameDetails->goods_name = "SortTest Start Item";
$pNameDetails->keyword = "";
$pNameDetails->save();

$pKeywordDetails->goods_name = "Other Item for Tag";
$pKeywordDetails->keyword = "SortTest";
$pKeywordDetails->save();

echo "Setup Complete.<br>";
echo "Product A (206718): Name='{$pNameDetails->goods_name}', Keyword='{$pNameDetails->keyword}'<br>";
echo "Product B (1898): Name='{$pKeywordDetails->goods_name}', Keyword='{$pKeywordDetails->keyword}'<br>";
echo "<a href='/goods/search?search_text=SortTest'>Search Now</a>";
