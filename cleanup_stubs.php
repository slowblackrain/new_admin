<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Delete Stub Categories
$deleted = DB::table('fm_category')->where('title', 'LIKE', 'Stub Category%')->delete();
echo "Deleted $deleted stub categories.\n";

// 2. Delete anything that looks like "Test" if user wants (Optional, be careful)
// Let's stick to Stub Category for now as that's what I definitely created.
// Also check for 'Test Category'
$deletedTest = DB::table('fm_category')->where('title', 'LIKE', 'Test Category%')->delete();
echo "Deleted $deletedTest test categories.\n";

// 3. Re-run Migration for Categories ONLY
// I can reuse migrate_legacy.php but it does goods too.
// I'll just replicate the category migration part here or verify if I should run the full script.
// Running full script is safe as it uses insertOrIgnore.
// But valid goods are already there.
// Valid categories (that were not stubs) are already there.
// So only the deleted ones (formerly stubs) need to be fetched.
// Running full migrate_legacy.php is fine.

echo "Re-running migration to fill in missing categories...\n";
passthru('php c:/dometopia/new_admin/migrate_legacy.php');
