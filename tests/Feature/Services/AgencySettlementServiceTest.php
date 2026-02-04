<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\Agency\AgencySettlementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class AgencySettlementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AgencySettlementService $service;
    protected $memberSeq;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AgencySettlementService();

        // Create Test Member
        $this->memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => 'settle_test',
            'user_name' => 'Settle Tester',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        // Seed Initial Cash
        DB::table('fm_cash')->insert([
            'member_seq' => $this->memberSeq,
            'type' => 'save',
            'gb' => 'plus',
            'cash' => 100000,
            'remain' => 100000,
            'regist_date' => now(),
            'memo' => 'Initial'
        ]);
    }

    public function test_deduct_agency_cash()
    {
        $orderSeq = 1001;
        $amount = 30000;

        $cashSeq = $this->service->deductAgencyCash($orderSeq, $this->memberSeq, $amount);

        $this->assertDatabaseHas('fm_cash', [
            'cash_seq' => $cashSeq,
            'member_seq' => $this->memberSeq,
            'gb' => 'minus',
            'cash' => $amount,
            'remain' => 70000, // 100000 - 30000
            'ordno' => $orderSeq
        ]);
    }

    public function test_deduct_agency_cash_insufficient_funds()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('예치금이 부족합니다');

        $this->service->deductAgencyCash(1002, $this->memberSeq, 150000); // More than 100000
    }

    public function test_refund_agency_cash()
    {
        // First deduct
        $this->service->deductAgencyCash(1003, $this->memberSeq, 30000);

        // Then refund
        $cashSeq = $this->service->refundAgencyCash(1003, $this->memberSeq, 30000);

        $this->assertDatabaseHas('fm_cash', [
            'cash_seq' => $cashSeq,
            'member_seq' => $this->memberSeq,
            'gb' => 'plus',
            'cash' => 30000,
            'remain' => 100000, // Back to original
            'ordno' => 1003
        ]);
    }

    public function test_settle_agency_margin()
    {
        $yearMonth = date('Y-m');
        $sellPrice = 20000;
        $providerPrice = 15000;

        $this->service->settleAgencyMargin($this->memberSeq, $yearMonth, $sellPrice, $providerPrice);

        $this->assertDatabaseHas('fm_account_provider_ats', [
            'member_seq' => $this->memberSeq,
            'acc_date' => $yearMonth,
            'sell_price' => 20000,
            'offer_price' => 15000,
            'margin' => 5000,
            'sell_ea' => 1
        ]);

        // Add another
        $this->service->settleAgencyMargin($this->memberSeq, $yearMonth, 30000, 22000);

        $this->assertDatabaseHas('fm_account_provider_ats', [
            'member_seq' => $this->memberSeq,
            'acc_date' => $yearMonth,
            'sell_price' => 50000, // 20000 + 30000
            'offer_price' => 37000, // 15000 + 22000
            'margin' => 13000, // 5000 + 8000
            'sell_ea' => 2
        ]);
    }
}
