<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\AffiliateSite;

echo "Starting cleanup of non-leaf mappings...\n";

// 1. Fetch valid leaf codes for all sites
$sites = AffiliateSite::where('is_active', 1)->get();
$serviceMap = [
    '대한판촉' => \App\Services\Affiliate\DaehanScraperService::class,
    '오너클랜' => \App\Services\Affiliate\OwnerclanService::class,
];

$validLeafCodes = [];

foreach ($sites as $site) {
    if (isset($serviceMap[$site->name])) {
        $serviceClass = $serviceMap[$site->name];
        $scraper = new $serviceClass();
        $categories = $scraper->fetchCategories();
        
        $leafCodes = [];
        if ($site->name === '대한판촉') {
            $daehanCategoryCodes = array_keys($categories);
            foreach ($categories as $code => $name) {
                $isLeaf = true;
                foreach ($daehanCategoryCodes as $otherCode) {
                    if ($code !== $otherCode && str_starts_with($otherCode, $code)) {
                        $isLeaf = false;
                        break;
                    }
                }
                if ($isLeaf) {
                    $leafCodes[] = (string)$code;
                }
            }
        } else if ($site->name === '오너클랜') {
            usort($categories, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            $count = count($categories);
            for ($i = 0; $i < $count; $i++) {
                if ($i < $count - 1 && str_starts_with($categories[$i+1]['name'], $categories[$i]['name'] . '>')) {
                    continue;
                }
                $leafCodes[] = (string)$categories[$i]['code'];
            }
        }
        
        $validLeafCodes[$site->id] = $leafCodes;
        echo "Found " . count($leafCodes) . " leaf categories for " . $site->name . ".\n";
    }
}

// 2. Find and delete mappings that are NOT in valid leaf codes
$mappings = DB::table('affiliate_category_mappings')->get();
$deletedCount = 0;

foreach ($mappings as $mapping) {
    $siteId = $mapping->affiliate_site_id;
    $affCode = (string)$mapping->affiliate_category_code;
    
    // If we have leaf codes for this site, and the mapped code is NOT a leaf
    if (isset($validLeafCodes[$siteId]) && !in_array($affCode, $validLeafCodes[$siteId])) {
        DB::table('affiliate_category_mappings')->where('id', $mapping->id)->delete();
        $deletedCount++;
        // echo "Deleted mapping ID {$mapping->id} (Code: {$affCode}, Name: {$mapping->affiliate_category_name})\n";
    }
}

echo "Cleanup complete. Deleted {$deletedCount} mappings pointing to non-leaf categories.\n";
