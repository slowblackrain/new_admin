<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Check fm_order (Expected Plain Text)
$order = \Illuminate\Support\Facades\DB::table('fm_order')->where('member_seq', 10)->first();
if ($order) {
    echo "Order Email (Plain): " . $order->order_email . "\n";
} else {
    echo "No Order found for member 10.\n";
}

// 2. Check fm_member (Encrypted)
$member = \Illuminate\Support\Facades\DB::table('fm_member')->where('userid', 'newjjang3')->first();
if ($member) {
    echo "Member Encrypted Email (Hex): " . bin2hex($member->email) . "\n";
    
    $keys = [
        'FirstMall',
        '90787b2345a52abd5dcac6da0e0e92c2', // webmail_key
        'dmtusr', // cid
        'fp98057', // sms_id
        '98057', // shopSno
        '88df5c797a0280709494d6a43d6bf728', // serial 1
        'dometopia1', // webmail_admin_id
        md5('dmtusr'),
        md5('fp98057'),
        md5('98057'),
        'firstmall',
        strtoupper('FirstMall'),
        'dometopia',
        substr('dometopia.com', 0, 16),
        'FirstMall_ShopKey', // guess
    ];
    
    foreach ($keys as $k) {
        $decrypted = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$member->email, $k]);
        $val = $decrypted[0]->d;
        echo "Key [{$k}]: " . ($val ? $val : '(null/garbage)') . "\n";
    }
}
