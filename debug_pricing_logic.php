<?php
// c:/dometopia/new_admin/debug_pricing_logic.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Goods;
use App\Models\GoodsOption;
use App\Services\PricingService;

// Mock Data for Verification
$product = new Goods();
$product->consumer_price = 20000; // Retail
$product->mtype_discount = 1000;  // Member Discount
$product->fifty_discount_ea = 50; 
$product->fifty_discount = 2000;  // Wholesale Discount Amount
$product->hundred_discount_ea = 100;
$product->hundred_discount = 5000; // Import Price Discount Amount

$option = new GoodsOption();
$option->price = 15000; // Wholesale Price

$service = new PricingService();
$pricing = $service->calculatePrice($product, $option, 1);

echo "=== Pricing Structure Verification ===\n";
echo "1. Retail Price (Somae): " . number_format($pricing['somae_price']) . "\n";
echo "2. Wholesale Price (Domae): " . number_format($pricing['domae_price']) . "\n";
echo "3. Wholesale Discount Price (50+): " . number_format($pricing['domae_discount_price']) . " (Calculated: " . number_format($pricing['domae_price']) . " - " . number_format($product->fifty_discount) . ")\n";
echo "4. Import Price (100+): " . number_format($pricing['suip_price']) . " (Calculated: " . number_format($pricing['domae_price']) . " - " . number_format($product->hundred_discount) . ")\n";

echo "\n--- Raw Data ---\n";
print_r($pricing);
