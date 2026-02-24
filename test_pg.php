<?php
$c = App()->make(App\Http\Controllers\Front\PaymentController::class);
$o = new App\Models\Order();
$o->setRelation('items', collect([ (object)['goods_seq' => 12345] ]));
$r = new ReflectionMethod($c, 'determinePg');
$r->setAccessible(true);
$params = $r->invoke($c, $o);
echo 'PG Selected Test 12345 (Pairing Expected): ' . $params['pg'] . "\n";

$o2 = new App\Models\Order();
$o2->setRelation('items', collect([ (object)['goods_seq' => 999999] ]));
$params2 = $r->invoke($c, $o2);
echo 'PG Selected Test 999999 (Toss Expected): ' . $params2['pg'] . "\n";
