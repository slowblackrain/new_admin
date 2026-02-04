<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\Order;
use App\Models\Goods;
use Illuminate\Support\Facades\Auth;

class AgencySettlementIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $buyer;
    protected $reseller;
    protected $atsGoods;
    protected $cartSeq;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Reseller (Agency Seller)
        $this->reseller = DB::table('fm_member')->insertGetId([
            'userid' => 'reseller_test',
            'user_name' => 'Reseller',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        // Reseller Cash Init
        DB::table('fm_cash')->insert([
            'member_seq' => $this->reseller,
            'type' => 'save',
            'gb' => 'plus',
            'cash' => 100000,
            'remain' => 100000,
            'regist_date' => now(),
            'memo' => 'Init'
        ]);

        // 2. Create ATS Product (Owned by Reseller)
        $this->atsGoods = new Goods();
        $this->atsGoods->goods_name = 'Test ATS Goods';
        $this->atsGoods->goods_code = 999123;
        $this->atsGoods->goods_scode = 'GT_TEST_123'; // GT prefix
        $this->atsGoods->provider_member_seq = $this->reseller; // Owner
        $this->atsGoods->goods_status = 'normal';
        $this->atsGoods->goods_view = 'look';
        $this->atsGoods->regist_date = now();
        $this->atsGoods->update_date = now();
        $this->atsGoods->goods_type = 'goods';
        $this->atsGoods->goods_kind = 'goods';
        $this->atsGoods->save();

        // ATS Option (Provider Price set)
        $optionSeq = DB::table('fm_goods_option')->insertGetId([
            'goods_seq' => $this->atsGoods->goods_seq,
            'option1' => 'Opt1',
            'price' => 20000, // Sell Price
            'provider_price' => 15000, // Supply Price (Cost)
            // 'regist_date' => now() // Column does not exist
        ]);
        
        // Stock
        DB::table('fm_goods_supply')->insert([
            'goods_seq' => $this->atsGoods->goods_seq,
            'option_seq' => $optionSeq,
            'supply_price' => 15000,
            'stock' => 100
        ]);

        // 3. Create Buyer (End User)
        $memberId = DB::table('fm_member')->insertGetId([
            'userid' => 'buyer_test',
            'user_name' => 'Buyer',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);
        $this->buyer = Member::find($memberId);
    }

    public function test_order_deducts_cash_and_confirm_settles_margin()
    {
        // A. Add to Cart (Directly DB)
        $cartSeq = DB::table('fm_cart')->insertGetId([
            'member_seq' => $this->buyer->member_seq,
            'goods_seq' => $this->atsGoods->goods_seq,
            'regist_date' => now()
        ]);

        DB::table('fm_cart_option')->insert([
            'cart_seq' => $cartSeq,
            // 'goods_seq' => $this->atsGoods->goods_seq, // Not in table
            'option1' => 'Opt1',
            'ea' => 2, // Qty 2
            // 'price' => 20000 // Not in table
        ]);

        // B. Place Order (OrderController::store)
        $response = $this->actingAs($this->buyer)->post('/order/pay', [
            'cart_seq' => [$cartSeq],
            'order_user_name' => 'TestBuyer',
            'order_cellphone' => '010-1234-5678',
            'order_email' => 'test@example.com',
            'recipient_user_name' => 'Receiver',
            'recipient_cellphone' => '010-1111-2222',
            'recipient_zipcode' => '12345',
            'recipient_address' => 'Seoul',
            'recipient_address_street' => 'Street',
            'recipient_address_detail' => 'Apt 101',
            'payment' => 'bank',
            'bank_account' => 'TestBank',
            'depositor' => 'TestDepositor'
        ]);

        // Assert Redirect (Success)
        // Note: It might redirect to /order/complete/{id}
        // $response->assertRedirect(); 
        
        // Get created order
        $order = Order::where('member_seq', $this->buyer->member_seq)->latest('regist_date')->first();
        $this->assertNotNull($order);

        // Verify Cash Deduction
        // 15000 * 2 = 30000 deducted
        $this->assertDatabaseHas('fm_cash', [
            'member_seq' => $this->reseller,
            'type' => 'order',
            'gb' => 'minus',
            'cash' => 30000
        ]);

        // C. Confirm Purchase (MypageController::confirmPurchase)
        // First set status to Delivered (so confirm logic works if checking status)
        $order->step = 65; // Delivered
        $order->save();

        $response = $this->actingAs($this->buyer)->post(route('mypage.order.confirm', $order->order_seq));
        $response->assertRedirect(); // Back or success

        // Verify Order Step
        $order->refresh();
        $this->assertEquals(75, $order->step);

        // Verify Settlement Margin
        // Sell: 20000 * 2 = 40000
        // Provider: 15000 * 2 = 30000
        // Margin: 10000
        $this->assertDatabaseHas('fm_account_provider_ats', [
            'member_seq' => $this->reseller,
            'sell_price' => 40000,
            'offer_price' => 30000,
            'margin' => 10000
        ]);
    }
}
