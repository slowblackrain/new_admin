<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Services\Agency\AgencyPriceCalculator;

$calc = new AgencyPriceCalculator();

// Test Case 1: Supply Price Calculation
// Original Supply Price: 10000 -> Expected: 11000 (10000 * 1.1)
$res1 = $calc->calculateSupplyPrice(10000);
echo "Supply Price (10000): " . $res1 . " (Expected: 11000)\n";

// Test Case 2: Rounding
// 12345 * 1.1 = 13579.5 -> Round(-1) -> 13580? Or 13580? Round -1 usually means nearest 10.
// 13579.5 -> 13580.
$res2 = $calc->calculateSupplyPrice(12345);
echo "Supply Price (12345): " . $res2 . " (Expected: 13580)\n";

// Test Case 3: Order Price
// Sell: 20000, Disc: 0 -> (20000 * 0.95) = 19000 -> * 1.1 = 20900 -> Round -> 20900.
$res3 = $calc->calculateDefaultOrderPrice(20000);
echo "Order Price (20000): " . $res3 . " (Expected: 20900)\n";

// Test Case 4: Order Price with rounding steps
// Sell: 12345 -> * 0.95 = 11727.75 -> Round(-1) = 11730 -> * 1.1 = 12903 -> Round(-1) = 12900.
$res4 = $calc->calculateDefaultOrderPrice(12345); 
echo "Order Price (12345): " . $res4 . " (Expected: 12900)\n";
