<?php

namespace Tests\Feature\Admin\Scm;

use Tests\TestCase;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScmOrderControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $seller;
    protected $goods;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Create Admin
        $this->admin = Member::factory()->create(['group_seq' => 1]); // Admin Group

        // 2. Create Seller (Provider)
        $this->seller = Member::factory()->create([
            'userid' => 'test_seller_scm',
            'cash' => 100000,
            'provider_YN' => 'Y',
            'group_seq' => 2 // Seller Group
        ]);

        // 3. Create goods mapped to Seller
        $this->goods = DB::table('fm_goods')->insertGetId([
            'goods_name' => 'SCM Test Product',
            'provider_seq' => $this->seller->member_seq,
            // 'goods_price' => 10000, // Removed
            'regist_date' => now(),
            'update_date' => now()
        ]);
        
        // 4. Default Supply Price Info
        DB::table('fm_scm_order_defaultinfo')->insert([
            'goods_seq' => $this->goods,
            'supply_price' => 8000,
            'main_trade_type' => 'Y',
            'trader_seq' => 1
        ]);
    }

    /** @test */
    public function it_deducts_cash_when_auto_order_created()
    {
        // Login as Admin
        $this->actingAs($this->admin, 'admin'); // Assuming 'admin' guard or similar, checking legacy.
        // Actually the controller uses default auth or specific middleware. 
        // AdminController usually checks session. Let's assume passed auth for now or simulate session.
        // Based on routes, it's under 'admin' prefix but middleware usage is mixed.
        // Let's try direct call with session.

        $initialCash = $this->seller->cash;
        $orderQty = 2;
        $supplyPrice = 8000;
        $expectedCost = round($supplyPrice * $orderQty * 1.1, -1);

        $response = $this->post(route('admin.scm_order.create'), [
            'orders' => [
                $this->goods => $orderQty
            ]
        ]);

        $response->assertRedirect();
        
        // Verify Cash Deduction
        $this->assertDatabaseHas('fm_member', [
            'member_seq' => $this->seller->member_seq,
            'cash' => $initialCash - $expectedCost
        ]);

        // Verify Cash Log
        $this->assertDatabaseHas('fm_cash', [
            'member_seq' => $this->seller->member_seq,
            'gb' => 'minus',
            'cash' => $expectedCost,
            'type' => 'order'
        ]);
    }

    /** @test */
    public function it_logs_failure_when_cash_is_insufficient()
    {
        // 1. Set 0 Cash
        DB::table('fm_member')->where('member_seq', $this->seller->member_seq)->update(['cash' => 0]);
        // Also ensure no logging, cause AgencySettlementService falls back to fm_member if log missing, 
        // but if log exists it takes precedence. 
        // Logic: getCurrentCash checks fm_cash logs. If I updated fm_member, but fm_cash has nothing, it reads fm_member (0).
        // If I had previous test run logs, I should be careful. DatabaseTransactions trait handles rollback.

        $response = $this->post(route('admin.scm_order.create'), [
            'orders' => [
                $this->goods => 2
            ]
        ]);

        $response->assertRedirect();
        
        // Verify Fail Log
        // Cost: 8000 * 2 * 1.1 = 17600
        $expectedCost = 17600;
        $this->assertDatabaseHas('fm_scm_order_fail_log', [
            'provider_seq' => $this->seller->member_seq,
            'goods_seq' => $this->goods,
            'fail_reason' => "예치금이 부족합니다. (필요: {$expectedCost}, 보유: 0)"
        ]);
    }

    /** @test */
    public function it_can_view_fail_logs_as_admin()
    {
        // 1. Create a failure log
        $failReason = 'Test Failure Reason ' . time();
        DB::table('fm_scm_order_fail_log')->insert([
            'goods_seq' => $this->goods,
            'provider_seq' => $this->seller->member_seq,
            'sorder_seq' => time(),
            'fail_reason' => $failReason,
            'regist_date' => now(),
            'is_checked' => 'N'
        ]);

        // 2. Visit Admin Page
        $this->actingAs($this->admin, 'admin');
        
        $response = $this->get(route('admin.scm_order.fail_log'));

        $response->assertStatus(200);
        $response->assertSee('자동발주 실패 내역');
        $response->assertSee($failReason);
        $response->assertSee($this->seller->userid);
    }
}
