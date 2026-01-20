try {
echo "Member Count: " . App\Models\Member::count() . PHP_EOL;
echo "Goods Count: " . App\Models\Goods::count() . PHP_EOL;
echo "Category Count: " . App\Models\Category::count() . PHP_EOL;
} catch (\Exception $e) {
echo "Error: " . $e->getMessage() . PHP_EOL;
}