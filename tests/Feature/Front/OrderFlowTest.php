<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Goods;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderFlowTest extends TestCase
{
    // use DatabaseTransactions; // Don't use this if we want to debug DB after test. Use manual cleanup or just let it persist for now.

    public function test_order_creation_with_bank_transfer()
    {
        // 1. Setup User
        $user = Member::where('userid', 'testuser')->first();
        if (!$user) {
            $this->fail('Test user not found. Run prepare_test_user.php first.');
        }

        // 2. Setup Goods and Option
        // We verified 211160 / 388112 in previous steps
        $goodsSeq = 211160;
        $optionSeq = 388112;

        // Seed Emoney
        $user->emoney = 10000;
        $user->save();

        // Seed Coupon
        $couponSeq = DB::table('fm_coupon')->insertGetId([
            'coupon_name' => 'Test Auto Coupon',
            'sale_type' => 'percent',
            'percent_goods_sale' => 10,
            'max_percent_goods_sale' => 5000,
            'issue_type' => 'manual',
            'use_type' => 'online',
            'regist_date' => now(),
            'update_date' => now()
        ]);

        $downloadSeq = DB::table('fm_download')->insertGetId([
            'member_seq' => $user->member_seq,
            'coupon_seq' => $couponSeq,
            'coupon_name' => 'Test Auto Coupon',
            'sale_type' => 'percent',
            'percent_goods_sale' => 10,
            'max_percent_goods_sale' => 5000,
            'type' => 'all',
            'use_status' => 'unused',
            'issue_startdate' => now()->subDay(),
            'issue_enddate' => now()->addDays(7),
            'regist_date' => now()
        ]);

        // 3. Add to Cart
        $response = $this->actingAs($user)->post(route('cart.store'), [
            'goods_seq' => $goodsSeq,
            'option_seq' => $optionSeq,
            'ea' => 2, // Buy 2 items
        ]);
        
        $response->assertStatus(302);
        
        $cart = Cart::where('member_seq', $user->member_seq)->orderBy('cart_seq', 'desc')->first();
        $this->assertNotNull($cart, 'Cart item should be created');
        $this->assertEquals(2, $cart->options->first()->ea, 'Cart quantity should be 2');

        // 4. Access Order Form
        $response = $this->actingAs($user)->post(route('order.form'), [
            'cart_seq' => [$cart->cart_seq]
        ]);
        $response->assertStatus(200);
        $response->assertSee('주문서 작성');
        
        // 5. Submit Order (Bank Transfer)
        $orderData = [
            'cart_seq' => [$cart->cart_seq],
            'order_user_name' => $user->user_name,
            'order_cellphone' => '010-1234-5678',
            'order_email' => 'test@example.com',
            'recipient_user_name' => 'Test Recipient',
            'recipient_cellphone' => '010-9876-5432',
            'recipient_zipcode' => '12345',
            'recipient_address' => 'Test Address City',
            'recipient_address_street' => 'Test Street',
            'recipient_address_detail' => '101',
            'recipient_address_type' => 'street',
            'memo' => 'Please deliver fast',
            'payment' => 'bank',
            'bank_account' => '국민은행 123-456-7890 도매토피아',
            'depositor' => 'Test Depositor',
            'use_emoney' => 5000,
            'download_seq' => $downloadSeq
        ];

        $response = $this->actingAs($user)->post(route('order.store'), $orderData);

        // 6. Verification
        if ($response->status() !== 302) {
             dump($response->getContent());
        }
        $response->assertStatus(302); 

        // Extract Order ID from Redirect URL
        $location = $response->headers->get('Location');
        $parts = explode('/', $location);
        $orderSeq = end($parts);
        if (strpos($orderSeq, '?') !== false) {
             $orderSeq = substr($orderSeq, 0, strpos($orderSeq, '?'));
        }

        // Check DB
        $order = \App\Models\Order::findOrFail($orderSeq);
        
        $this->assertNotNull($order, 'Order should be created');
        $this->assertEquals('12345', $order->recipient_zipcode);
        $this->assertEquals('bank', $order->payment);
        
        $this->assertEquals(2, $order->total_ea);
        $this->assertEquals(5000, $order->emoney, 'Order record should show 5000 emoney used');
        
        // Validate Coupon Sale
        // Price: 89630 * 2 = 179260. 
        // 10% Coupon = 17926. Max Cap = 5000. So valid discount is 5000.
        $this->assertEquals(5000, $order->coupon_sale, 'Coupon Discount should be capped at 5000');
        
        // Use redirect target verification
        $response->assertRedirect(route('order.complete', ['id' => $order->order_seq]));

        // Check Emoney Decrement (Used 5000)
        $user->refresh();
        $this->assertEquals(5000, $user->emoney, 'User emoney should be 5000');
        
        // Check Coupon Used
        $dl = DB::table('fm_download')->where('download_seq', $downloadSeq)->first();
        $this->assertEquals('used', $dl->use_status, 'Coupon should be marked as used');

        // Check Cart Deleted
        $cartParams = Cart::where('cart_seq', $cart->cart_seq)->first();
        $this->assertNull($cartParams, 'Cart item should be deleted after order');

        echo "\nOrder Test Passed! Order Seq: " . $order->order_seq . "\n";
    }
}
