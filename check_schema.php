<?php

use Illuminate\Support\Facades\DB;

$columns = DB::select("DESCRIBE fm_manager");
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
