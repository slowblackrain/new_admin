<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Member;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MypageOrderTest extends TestCase
{
    // use DatabaseTransactions; 

    public function test_mypage_shows_order_list()
    {
        // 1. Setup Data
        $user = Member::where('userid', 'testuser')->first();
        if (!$user) {
             $this->markTestSkipped('Test user not found');
        }

        $orderSeq = date('YmdHis') . rand(10000, 99999);
        $order = new Order();
        $order->order_seq = $orderSeq;
        $order->member_seq = $user->member_seq;
        $order->order_user_name = $user->user_name;
        $order->regist_date = now();
        $order->settleprice = 30000;
        $order->step = 15; // Order Received
        $order->payment = 'bank';
        $order->recipient_user_name = 'Receiver';
        $order->recipient_cellphone = '010-0000-0000';
        $order->recipient_address = 'Address';
        $order->save();

        // Items (Optional for list, but good for view)
        // ...

        // 2. Visit My Page Order List
        $response = $this->actingAs($user)->get(route('mypage.order.list'));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee($orderSeq);
        $response->assertSee(number_format(30000));
        
        // 4. Visit Detail View
        $responseView = $this->actingAs($user)->get(route('mypage.order.view', $orderSeq));
        $responseView->assertStatus(200);
        $responseView->assertSee($orderSeq);
        $responseView->assertSee('Receiver');

        // Cleanup
        $order->delete();
    }
}
