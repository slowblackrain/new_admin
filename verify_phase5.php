try {
echo "=== Goods Verification ===\n";
// Added 'images' to eager load
$goods = App\Models\Goods::active()->with(['option', 'images'])->orderBy('regist_date', 'desc')->first();
if ($goods) {
echo "Found Goods: " . $goods->goods_name . "\n";
echo "Image URL: " . $goods->image . "\n";
echo "Price: " . $goods->price . " (Consumer: " . $goods->consumer_price . ")\n";
echo "Option Loaded: " . ($goods->option ? 'Yes' : 'No') . "\n";
echo "Images Loaded: " . ($goods->images->count() > 0 ? 'Yes: '.$goods->images->count() : 'No') . "\n";
} else {
echo "No Active Goods Found.\n";
}

echo "\n=== Category Verification ===\n";
$cats = App\Models\Category::whereRaw('length(category_code) = 4')->limit(5)->get();
echo "Top Categories: " . $cats->count() . "\n";
foreach($cats as $cat) {
echo "- " . ($cat->title ?? $cat->category_code) . "\n";
}

} catch (\Exception $e) {
echo "ERROR: " . $e->getMessage() . "\n";
}