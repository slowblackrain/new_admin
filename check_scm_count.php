<?php
require 'c:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'c:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('fm_scm_location_link')->count();
echo "SCM Location Link Count: " . $count . "\n";
