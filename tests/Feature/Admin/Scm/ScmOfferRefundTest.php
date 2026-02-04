<?php

namespace Tests\Feature\Admin\Scm;

use Tests\TestCase;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use App\Services\Agency\AgencySettlementService;

class ScmOfferRefundTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Admin user exists
    }

    public function test_agency_refund_on_offer_cancel()
    {
        // 1. Create Agency Seller (Member)
        $memberId = 'agency_test_' . rand(1000,9999);
        $memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => $memberId,
            'user_name' => 'Agency Tester',
            'status' => 'active',
            'cash' => 100000, // Initial Cash
            'regist_date' => now(),
        ]);

        // 2. Create Provider linked to Member
        $providerSeq = DB::table('fm_provider')->insertGetId([
            'provider_id' => $memberId,
            'userid' => $memberId, // Link to Member
            'provider_name' => 'Agency Provider',
            'regdate' => now(),
        ]);

        // 3. Create Goods (Agency Product)
        $goodsSeq = DB::table('fm_goods')->insertGetId([
            'goods_name' => 'Agency Test Goods',
            'provider_seq' => $providerSeq,
            'regist_date' => now(),
        ]);
        
        // 4. Create SCM Default Info (Cost Price)
        DB::table('fm_scm_order_defaultinfo')->insert([
            'goods_seq' => $goodsSeq,
            'supply_price' => 20000, 
            'trader_seq' => 1,
            'regist_date' => now(),
            'use_status' => 'Y'
        ]);

        // 5. Create fm_offer (Simulate Order)
        $sno = DB::table('fm_offer')->insertGetId([
            'goods_seq' => $goodsSeq,
            'step' => 100, // Agency Step
            'ord_total' => '|1|', // Qty 1 (Pipe format)
            'ord_shipping' => 3000,
            'ord_supply' => 20000,
            'regist_date' => now(),
        ]);

        // 6. Act: Cancel Offer via Controller Route
        // We need an Admin user to hit the route
        $admin = Member::factory()->create(['group_seq' => 1]); 
        
        $response = $this->actingAs($admin, 'admin')->post(route('admin.scm_order.update_status'), [
            'action' => 'cancel',
            'chk' => [$sno],
        ]);

        // 7. Assert: 
        // a) Offer Deleted
        $this->assertDatabaseMissing('fm_offer', ['sno' => $sno]);

        // b) Refund Record in fm_cash
        // Expected Refund: 20000 * 1 + 3000 = 23000
        $this->assertDatabaseHas('fm_cash', [
            'member_seq' => $memberSeq,
            'cash' => 23000,
            'gb' => 'plus',
            'type' => 'order', // or cancel? Service uses 'order'
        ]);

        // c) Member Cash Increased
        // Initial 100000 + 23000 = 123000
        $finalCash = DB::table('fm_member')->where('member_seq', $memberSeq)->value('cash');
        $this->assertEquals(123000, $finalCash, "Member cash should increase by 23000");
    }
}
