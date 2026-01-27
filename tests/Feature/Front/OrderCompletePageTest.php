<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Member;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderCompletePageTest extends TestCase
{
    // use DatabaseTransactions; // Persistent for manual check if needed

    public function test_order_complete_page_renders_correctly()
    {
        // 1. Create a dummy order
        $user = Member::where('userid', 'testuser')->first();
        if (!$user) {
             // Fallback or skip if not seeded
             $this->markTestSkipped('Test user not found');
        }

        $orderSeq = date('YmdHis') . rand(10000, 99999);
        $order = new Order();
        $order->order_seq = $orderSeq;
        $order->order_user_name = 'Test User';
        $order->regist_date = now();
        $order->settleprice = 50000;
        $order->payment = 'bank';
        $order->bank_account = 'Test Bank 123';
        $order->depositor = 'Test Depositor';
        $order->recipient_user_name = 'Recipient';
        $order->recipient_cellphone = '010-0000-0000';
        $order->recipient_address = 'Addr';
        $order->step = 15;
        $order->save();

        // 2. Visit Page
        $response = $this->actingAs($user)->get(route('order.complete', ['id' => $orderSeq]));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('주문이 정상적으로 접수되었습니다');
        $response->assertSee($orderSeq);
        $response->assertSee('50,000원');
        $response->assertSee('무통장 입금');
        $response->assertSee('Test Bank 123');

        // Cleanup
        $order->delete();
    }
}
