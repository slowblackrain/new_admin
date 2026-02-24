<?php
$req = Illuminate\Http\Request::create('/cart/ats-batch', 'POST', ['goods_seq_list' => '1000064']);
$res = app()->make(App\Http\Controllers\Front\CartController::class)->addAtsBatch($req);
echo $res->getContent();
