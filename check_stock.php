<?php
$opts = DB::table('fm_goods_option')->where('goods_seq', 1000064)->get();
echo "Found " . count($opts) . " options.\n";

foreach($opts as $o) {
    echo "Option Seq: " . $o->option_seq . "\n";
    $s = DB::table('fm_goods_supply')->where('option_seq', $o->option_seq)->first();
    if ($s) {
        echo " Supply Seq: " . $s->supply_seq . " | Stock: " . $s->stock . "\n";
    } else {
        echo " No Supply Record!\n";
    }
}
