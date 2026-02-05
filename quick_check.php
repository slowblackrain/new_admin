<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols1 = Illuminate\Support\Facades\Schema::getColumnListing('fm_scm_stock_revision');
dump($cols1);
$cols2 = Illuminate\Support\Facades\Schema::getColumnListing('fm_scm_stock_revision_goods');
dump($cols2);
