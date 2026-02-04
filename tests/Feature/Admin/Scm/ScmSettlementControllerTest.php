<?php

namespace Tests\Feature\Admin\Scm;

use Tests\TestCase;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScmSettlementControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Admin
        $this->admin = Member::factory()->create(['group_seq' => 1]);
    }

    /** @test */
    public function it_can_list_agency_settlements()
    {
        // 1. Create a Provider (Agency)
        $provider = Member::factory()->create([
            'userid' => 'test_provider_ats',
            'user_name' => 'Test Provider',
            'group_seq' => 2
        ]);

        // 2. Create Settlement Data
        $yearMonth = date('Y-m');
        DB::table('fm_account_provider_ats')->insert([
            'acc_date' => $yearMonth,
            'member_seq' => $provider->member_seq,
            'sell_price' => 100000,
            'offer_price' => 88000,
            'margin' => 12000,
            'sell_ea' => 10,
            'acc_status' => 'none',
            'regist_date' => now(),
        ]);

        // 3. Act as Admin
        $this->actingAs($this->admin, 'admin');

        // 4. Request
        $response = $this->get(route('admin.scm_settlement.index'));

        // 5. Assert
        $response->assertStatus(200);
        $response->assertSee('입점사 정산 관리 (ATS)');
        $response->assertSee('Test Provider');
        $response->assertSee('100,000원');
        $response->assertSee('12,000원');
    }
}
