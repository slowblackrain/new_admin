<?php
use App\Models\Goods;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$keyword = 'SortTest';

// Simulate the Query from GoodsController
$query = Goods::on('production')->active();

// Apply Keyword Logic
$query->where(function ($q) use ($keyword) {
    $q->orWhere('goods_name', 'like', "%{$keyword}%")
      ->orWhere('goods_code', 'like', "%{$keyword}%")
      ->orWhere('goods_scode', 'like', "%{$keyword}%")
      ->orWhere('keyword', 'like', "%{$keyword}%");
});

// Output Query
echo "<h1>Debug Search Query</h1>";
echo "<h3>SQL:</h3>";
echo $query->toSql();
echo "<h3>Bindings:</h3>";
print_r($query->getBindings());

// Run and Check Count
$count = $query->count();
echo "<h3>Count Results: $count</h3>";

// Run and Show Results
$results = $query->get();
echo "<h3>Results:</h3>";
foreach($results as $item) {
    echo "ID: " . $item->goods_seq . " | Name: " . $item->goods_name . "<br>";
}
