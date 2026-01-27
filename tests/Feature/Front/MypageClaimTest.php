<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class MypageClaimTest extends TestCase
{
    public function test_user_can_cancel_order()
    {
        // 1. Setup User
        $user = Member::where('userid', 'testuser')->first();
        if (!$user) {
            $user = new Member();
            $user->userid = 'testuser';
            $user->user_name = 'Test User';
            $user->email = 'test@example.com';
            $user->password = bcrypt('password');
            $user->status = 'active';
            $user->save();
        }

        // 2. Create Order (Step 15 - Cancelable)
        $orderSeq = date('YmdHis') . rand(1000, 9999);
        $order = new Order();
        $order->order_seq = $orderSeq;
        $order->member_seq = $user->member_seq;
        $order->order_user_name = $user->user_name;
        $order->order_cellphone = '010-1234-5678';
        $order->order_email = $user->email;
        $order->recipient_user_name = 'Recipient';
        $order->recipient_cellphone = '010-9876-5432';
        $order->recipient_zipcode = '12345';
        $order->recipient_address = 'Test Address';
        $order->step = 15; // Deposit Pending
        // $order->total_price = 10000; // Removed incorrect column
        $order->settleprice = 10000;
        $order->payment = 'bank';
        $order->regist_date = now();
        $order->save();

        // 3. Access Claim Page
        $response = $this->actingAs($user)->get(route('mypage.claim.apply', ['orderSeq' => $orderSeq, 'type' => 'cancel']));
        $response->assertStatus(200);
        $response->assertSee('주문취소');
        
        // 4. Submit Claim
        $response = $this->actingAs($user)->post(route('mypage.claim.store', ['orderSeq' => $orderSeq]), [
            'type' => 'cancel',
            'reason' => '단순변심',
            'reason_detail' => 'Just changed my mind',
            'items' => [1] // Dummy item ID check (controller validates only existence currently)
        ]);

        $response->assertRedirect(route('mypage.order.view', $orderSeq));
        
        // 5. Verify DB Update
        $order->refresh();
        // Since step < 25, logic sets it to 95 (Cancel Complete)
        $this->assertEquals(95, $order->step, 'Order step should be 95 (Cancel Complete)');
        $this->assertStringContainsString('단순변심', $order->admin_memo);
        
        // Cleanup
        $order->delete();
    }
}
