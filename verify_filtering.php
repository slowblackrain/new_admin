try {
echo "=== Category Filtering Verification ===\n";

// Pick a test category (first 4-digit one)
$cat = App\Models\Category::whereRaw('length(category_code) = 4')->first();
if (!$cat) {
throw new Exception("No top-level categories found.");
}

echo "Testing Category: " . $cat->title . " (" . $cat->category_code . ")\n";

// Count goods via filter
$count = App\Models\Goods::active()->whereHas('categoryLinks', function($q) use ($cat) {
$q->where('category_code', 'like', $cat->category_code . '%');
})->count();

echo "Goods Count in Category: " . $count . "\n";

if ($count > 0) {
$firstGood = App\Models\Goods::active()->whereHas('categoryLinks', function($q) use ($cat) {
$q->where('category_code', 'like', $cat->category_code . '%');
})->first();
echo "Example Good: " . $firstGood->goods_name . "\n";
} else {
echo "No goods found in this category.\n";
}

} catch (\Exception $e) {
echo "ERROR: " . $e->getMessage() . "\n";
}