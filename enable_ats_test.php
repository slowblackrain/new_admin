<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$userid = 'parksh73';
$member = DB::table('fm_member')->where('userid', $userid)->first();

if ($member) {
    echo "Found Member: $userid (Current provider_YN: " . $member->provider_YN . ")\n";
    DB::table('fm_member')->where('userid', $userid)->update(['provider_YN' => 'Y']);
    echo "Updated provider_YN to 'Y'.\n";
    
    // Check again
    $member = DB::table('fm_member')->where('userid', $userid)->first();
    echo "New provider_YN: " . $member->provider_YN . "\n";
} else {
    echo "Member $userid not found in fm_member.\n";
}
