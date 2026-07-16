<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ownerclanService = new \App\Services\Affiliate\OwnerclanService();
// Fetch categories (will fetch from API and cache to file)
echo "Fetching Ownerclan categories...\n";
$categories = $ownerclanService->fetchCategories();

echo "Total Ownerclan categories fetched: " . count($categories) . "\n";

$site = \App\Models\AffiliateSite::where('name', '오너클랜')->first();
if (!$site) {
    echo "Site '오너클랜' not found\n";
    exit;
}

$mappings = \App\Models\AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
    ->whereNull('affiliate_category_name')
    ->get();

echo "Found " . $mappings->count() . " mappings to update.\n";

$catMap = [];
foreach ($categories as $cat) {
    $catMap[$cat['code']] = $cat['name'];
}

$updated = 0;
foreach ($mappings as $mapping) {
    if (isset($catMap[$mapping->affiliate_category_code])) {
        $mapping->affiliate_category_name = $catMap[$mapping->affiliate_category_code];
        $mapping->save();
        $updated++;
    }
}

echo "Updated " . $updated . " mapping names.\n";
