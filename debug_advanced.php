<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Advanced Debug ===\n";

// 1. Board Analysis
echo "[Board Analysis]\n";
$boardId = 'gs_seller_notice';
$boardConfig = DB::table('fm_boardadmin')->where('id', $boardId)->first();

if ($boardConfig) {
    echo "Board Config Found for '$boardId':\n";
    echo "  - Title: " . $boardConfig->title . "\n"; // assuming title exists
    echo "  - Table Name used: " . ($boardConfig->board_table_name ?? 'Use Default') . "\n"; 
} else {
    echo "Board Config for '$boardId' NOT FOUND in fm_boardadmin.\n";
    echo "First 5 boards in admin:\n";
    $boards = DB::table('fm_boardadmin')->limit(5)->get();
    foreach($boards as $b) {
        echo "  - " . $b->id . " (" . ($b->title ?? 'No Title') . ")\n";
    }
}

// Check fm_boarddata
echo "\n[fm_boarddata check]\n";
if (DB::getSchemaBuilder()->hasTable('fm_boarddata')) {
    $count = DB::table('fm_boarddata')->count();
    echo "fm_boarddata has $count rows.\n";
    if ($count > 0) {
        $sample = DB::table('fm_boarddata')->first();
        echo "Sample columns: " . implode(', ', array_keys((array)$sample)) . "\n";
    }
}

// 2. Find a Valid Provider for Testing
echo "\n[Provider Search]\n";
// Find any provider with a non-empty userid
$validProvider = DB::table('fm_provider')->where('userid', '!=', '')->first();

if ($validProvider) {
    echo "Found Valid Provider:\n";
    echo "  - ID: " . $validProvider->provider_id . "\n";
    echo "  - UserID: " . $validProvider->userid . "\n";
    
    // Check if member exists
    $member = DB::table('fm_member')->where('userid', $validProvider->userid)->first();
    if ($member) {
        echo "  - Member Found! Seq: " . $member->member_seq . "\n";
    } else {
        echo "  - Member NOT FOUND for this userid.\n";
    }
} else {
    echo "No provider with a userid found.\n";
}

// 3. Member Search (Backup)
// Is there a 'dometopia' member?
$domeMember = DB::table('fm_member')->where('userid', 'dometopia')->first();
if ($domeMember) {
    echo "\n'dometopia' Member exists! Seq: " . $domeMember->member_seq . "\n";
}
