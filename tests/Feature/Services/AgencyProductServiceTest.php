<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\GoodsImage;
use App\Models\CategoryLink;
use App\Services\Agency\AgencyProductService;
use App\Services\Agency\AgencyPriceCalculator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class AgencyProductServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AgencyProductService $agencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agencyService = app(AgencyProductService::class);
    }

    public function test_duplicate_product_creates_valid_gt_product()
    {
        // 1. Setup Data
        // Create Reseller Member & Provider
        $memberId = 'reseller_test';
        $memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => $memberId,
            'user_name' => 'Reseller Test',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        $providerSeq = DB::table('fm_provider')->insertGetId([
            'userid' => $memberId,
            'provider_name' => 'Reseller Provider',
            'provider_status' => 'Y',
            'regdate' => now(),
        ]);

        // Create Source ATS Product
        $sourceGoodsSeq = DB::table('fm_goods')->insertGetId([
            'goods_name' => 'Source ATS Product',
            'goods_code' => 12345, // Integer Legacy Code
            'goods_scode' => 'ATS12345', // String Code (Prefix Logic)
            'goods_status' => 'normal',
            'goods_view' => 'look',
            'provider_seq' => 1, // System provider or other supplier
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        // Create Source Options
        $optionSeq = DB::table('fm_goods_option')->insertGetId([
            'goods_seq' => $sourceGoodsSeq,
            'consumer_price' => 20000,
            'price' => 15000,
        ]);

        // Create Source Supply Record
        DB::table('fm_goods_supply')->insert([
            'goods_seq' => $sourceGoodsSeq,
            'option_seq' => $optionSeq,
            'supply_price' => 10000,
            'stock' => 100,
            'total_stock' => 100
        ]);

        // 2. Execute Duplication
        $newGoods = $this->agencyService->duplicateProduct($sourceGoodsSeq, $memberSeq);

        // 3. Verify Goods Data
        $this->assertEquals('GT12345', $newGoods->goods_scode);
        $this->assertEquals('Source ATS Product  가등록', $newGoods->goods_name);
        $this->assertEquals('notLook', $newGoods->goods_view);
        $this->assertEquals('unsold', $newGoods->goods_status);
        $this->assertEquals($providerSeq, $newGoods->provider_seq);
        $this->assertEquals(0, $newGoods->tot_stock);
        
        // Check traceability
        // We'll query raw DB as 'old_goods_seq' might not be in Goods model fillable/properties if legacy model unused
        $rawGoods = DB::table('fm_goods')->where('goods_seq', $newGoods->goods_seq)->first();
        $this->assertEquals($sourceGoodsSeq, $rawGoods->old_goods_seq);

        // 4. Verify Options & Pricing
        $newOption = DB::table('fm_goods_option')->where('goods_seq', $newGoods->goods_seq)->first();
        $this->assertNotNull($newOption);
        // Default margin 10%: 10000 * 1.1 = 11000
        $this->assertEquals(11000, $newOption->provider_price);
        
        // Check Supply Record for new product
        $newSupply = DB::table('fm_goods_supply')->where('option_seq', $newOption->option_seq)->first();
        $this->assertEquals(0, $newSupply->stock);
        $this->assertEquals(11000, $newSupply->supply_price);
        
        // 5. Verify Source Suspension
        $updatedSource = Goods::find($sourceGoodsSeq);
        $this->assertEquals('notLook', $updatedSource->goods_view);
        $this->assertEquals('unsold', $updatedSource->goods_status);

        // 6. Verify Offer Record
        $offerCallback = DB::table('fm_offer')
            ->where('goods_seq', $newGoods->goods_seq)
            ->where('step', 11)
            ->exists();
        $this->assertTrue($offerCallback, 'Offer record with step 11 should exist');
    }

    public function test_pricing_calculator_logic()
    {
        $calculator = new AgencyPriceCalculator();

        // Test Default 10%
        $this->assertEquals(11000, $calculator->calculateSupplyPrice(10000));
        
        // Test Custom 20%
        $this->assertEquals(12000, $calculator->calculateSupplyPrice(10000, 20));

        // Test Rounding (Nearest 10)
        // 10000 * 1.15 = 11500 (No rounding needed)
        // 12345 * 1.1 = 13579.5 -> Round(-1) -> 13580
        
        // Logic check: 
        // 1234 * 1.1 = 1357.4 
        // round(1357.4, -1) = 1360
        
        $this->assertEquals(1360, $calculator->calculateSupplyPrice(1234));
    }
}
