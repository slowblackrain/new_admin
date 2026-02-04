<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Agency\AgencySettlementService;

class OrderFailureNotificationTest extends TestCase
{
    public function test_low_balance_triggers_notification()
    {
        // 1. Create Agency Seller with LOW CASH
        $sellerId = 'fail_seller_' . rand(1000,9999);
        $sellerSeq = DB::table('fm_member')->insertGetId([
            'userid' => $sellerId,
            'user_name' => 'Poor Seller',
            'status' => 'active',
            'cash' => 0, // Zero Cash
            'regist_date' => now(),
        ]);
        
        $providerSeq = DB::table('fm_provider')->insertGetId([
            'provider_id' => $sellerId,
            'userid' => $sellerId,
            'provider_name' => 'Poor Provider',
            'regdate' => now(),
        ]);

        // 2. Create ATS Product linked to Seller
        $randomCode = 'GT_FAIL_' . rand(10000, 99999);
        $randomInt = rand(100000, 999999); // Ensure unique int for goods_code
        $goodsSeq = DB::table('fm_goods')->insertGetId([
            'goods_name' => 'Expensive Agency Goods',
            'goods_code' => $randomInt,
            'goods_scode' => $randomCode, // Matches OrderController logic
            'provider_seq' => $providerSeq, // Linked to Seller
            'provider_member_seq' => $sellerSeq, // Important for Agency Logic
            'regist_date' => now(),
            'tot_stock' => 100
        ]);
        
        // Option
        $optionSeq = DB::table('fm_goods_option')->insertGetId([
            'goods_seq' => $goodsSeq,
            'consumer_price' => 50000,
            'price' => 50000,
            'provider_price' => 30000, // Supply Price to Agency
            'option1' => 'Default'
        ]);

        // Stock Supply (Required for OrderController line 147)
        DB::table('fm_goods_supply')->insert([
            'goods_seq' => $goodsSeq,
            'option_seq' => $optionSeq,
            'stock' => 100
        ]);

        // 3. Create Buyer
        $buyer = Member::factory()->create(['user_name' => 'Rich Buyer']);
        
        // 4. Create Cart
        $cartSeq = DB::table('fm_cart')->insertGetId([
            'member_seq' => $buyer->member_seq,
            'goods_seq' => $goodsSeq,
            'session_id' => 'test_session',
            'distribution' => 'cart',
            'regist_date' => now(),
            'update_date' => now()
        ]);
        
        DB::table('fm_cart_option')->insert([
            'cart_seq' => $cartSeq,
            'ea' => 1,
            'option1' => 'Default'
        ]);

        // 5. Act: Attempt Purchase
        // We expect a redirection back with error, AND a log entry.
        $response = $this->actingAs($buyer)->post(route('order.store'), [
            'cart_seq' => [$cartSeq],
            'order_user_name' => 'Buyer',
            'order_cellphone' => '010-1234-5678',
            'order_email' => 'buyer@test.com',
            'recipient_user_name' => 'Receiver',
            'recipient_cellphone' => '010-9876-5432',
            'recipient_zipcode' => '12345',
            'recipient_address' => 'Test Address',
            'payment' => 'bank',
            'bank_account' => 'Test Bank',
            'depositor' => 'Buyer'
        ]);

        // 6. Assertions
        // a) Should redirect back with error
        $response->assertStatus(302);
        
        // b) Check Fail Log
        $this->assertDatabaseHas('fm_scm_order_fail_log', [
            'goods_seq' => $goodsSeq,
            'provider_seq' => $sellerSeq, // Log stores member_seq as provider_seq (matches Dashboard logic)
            'fail_reason' => '예치금이 부족합니다. (필요: 30000, 보유: 0)',
            'is_checked' => 'N'
        ]);
    }
}
