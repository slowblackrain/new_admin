<?php

namespace Tests\Feature\Seller;

use Tests\TestCase;
use App\Models\Seller;
use App\Models\Goods;
use App\Services\Agency\AgencyProductService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mockery;

class ATSControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $seller;
    protected $memberSeq;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Seller and Member linkage
        $userid = 'ats_tester';
        $this->memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => $userid,
            'user_name' => 'ATS Tester',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        $providerSeq = DB::table('fm_provider')->insertGetId([
            'userid' => $userid, // Links to member
            'provider_id' => $userid,
            'provider_name' => 'ATS Test Provider',
            'provider_status' => 'Y',
            'regdate' => now(),
        ]);

        // Manually hydrate Seller model for actingAs
        $this->seller = new Seller();
        $this->seller->provider_seq = $providerSeq;
        $this->seller->provider_id = $userid;
        $this->seller->userid = $userid;
        $this->seller->exists = true;
    }

    public function test_catalog_screen_loads()
    {
        $response = $this->actingAs($this->seller, 'seller')->get(route('seller.ats.catalog'));
        $response->assertStatus(200);
        $response->assertViewIs('seller.ats.catalog');
    }

    public function test_copy_action_calls_service_success()
    {
        // Mock Service
        $mockService = Mockery::mock(AgencyProductService::class);
        $sourceGoodsSeq = 12345;

        // Expect duplicateProduct call
        $mockGoods = new Goods();
        $mockGoods->goods_seq = 99999;
        $mockGoods->goods_scode = 'GT12345';

        $mockService->shouldReceive('duplicateProduct')
            ->once()
            ->with($sourceGoodsSeq, $this->memberSeq)
            ->andReturn($mockGoods);

        // Swap instance
        $this->app->instance(AgencyProductService::class, $mockService);

        // Request
        $response = $this->actingAs($this->seller, 'seller')->postJson(route('seller.ats.copy'), [
            'goods_seq' => $sourceGoodsSeq
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'new_goods_seq' => 99999,
                'new_goods_scode' => 'GT12345'
            ]);
    }

    public function test_copy_action_validation_fails()
    {
        $response = $this->actingAs($this->seller, 'seller')->postJson(route('seller.ats.copy'), []);
        $response->assertStatus(422); // Validation error
    }
}
