<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PricingService;
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PricingServiceTest extends TestCase
{
    protected $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    // 1. Staff Pricing Test
    public function test_staff_pricing_group_2()
    {
        // Mock User as Staff (Group 2)
        $user = new User();
        $user->group_seq = 2;
        $user->mtype = 'business'; // Often staff are business too, but group_seq priority check
        Auth::shouldReceive('user')->andReturn($user);

        // Case A: GK Prefix (Price = cbm[6] + cbm[16])
        $product = new Goods();
        $product->price = 10000;
        $product->goods_scode = 'GK12345';
        // cbm string: 0|1|2|3|4|5|6000|7|8|9|10|11|12|13|14|15|500
        // Expected: 6000 + 500 = 6500
        $product->multi_discount_cbm = '0|1|2|3|4|5|6000|7|8|9|10|11|12|13|14|15|500';

        $result = $this->pricingService->calculatePrice($product, null, 1);
        $this->assertEquals(6500, $result['price']);
        $this->assertEquals('staff_special', $result['discount_type']);

        // Case B: GT Prefix (Price = round(cbm[6] * 0.8, -1) + cbm[16])
        $product2 = new Goods();
        $product2->price = 20000;
        $product2->goods_scode = 'GT12345';
        // cbm[6] = 10000. 10000 * 0.8 = 8000. Round(-1) = 8000. + 500 = 8500.
        $product2->multi_discount_cbm = '0|1|2|3|4|5|10000|7|8|9|10|11|12|13|14|15|500';

        $result2 = $this->pricingService->calculatePrice($product2, null, 1);
        $this->assertEquals(8500, $result2['price']);
    }

    // 2. Volume Discount Test (Dynamic Thresholds)
    public function test_volume_discounts_dynamic()
    {
        Auth::shouldReceive('user')->andReturn(null); // Guest/Retail

        // Mock Goods to handle getPriceAttribute
        $product = \Mockery::mock(Goods::class)->makePartial();
        $product->shouldReceive('getAttribute')->with('price')->andReturn(10000);
        $product->shouldReceive('getAttribute')->with('consumer_price')->andReturn(12000);
        $product->shouldReceive('getAttribute')->with('mtype_discount')->andReturn(0);
        
        // Dynamic Thresholds
        $product->fifty_discount = 1000;
        $product->hundred_discount = 2000;
        $product->fifty_discount_ea = 10;
        $product->hundred_discount_ea = 20;

        // Case A: Below Tier 1 (5 items) -> Retail Price
        $result = $this->pricingService->calculatePrice($product, null, 5);
        $this->assertEquals(10000, $result['price']);

        // Case B: Tier 1 (15 items) -> 9000
        $result = $this->pricingService->calculatePrice($product, null, 15);
        $this->assertEquals(9000, $result['price']);
        $this->assertEquals('volume_tier_1', $result['discount_type']);

        // Case C: Tier 2 (25 items) -> 8000
        $result = $this->pricingService->calculatePrice($product, null, 25);
        $this->assertEquals(8000, $result['price']);
        $this->assertEquals('volume_tier_2', $result['discount_type']);
    }

    // 3. Retail >99k Rule Test
    public function test_retail_99k_rule()
    {
        // Mock Retail User
        $user = new User();
        $user->mtype = 'general';
        $user->group_seq = 8;
        Auth::shouldReceive('user')->andReturn($user);

        $product = \Mockery::mock(Goods::class)->makePartial();
        $product->shouldReceive('getAttribute')->with('price')->andReturn(50000);
        $product->shouldReceive('getAttribute')->with('consumer_price')->andReturn(60000);

        $product->mtype_discount = 5000; // Wholesale discount
        $product->fifty_discount_ea = 0;
        $product->hundred_discount_ea = 0;

        // Case A: Total < 99k (1 item) -> Retail Price
        $result = $this->pricingService->calculatePrice($product, null, 1);
        $this->assertEquals(50000, $result['price']);

        // Case B: Total > 99k (2 items = 100k) -> Wholesale Price
        $result = $this->pricingService->calculatePrice($product, null, 2);
        // Expected: 50000 - 5000 = 45000
        $this->assertEquals(45000, $result['price']);
        $this->assertEquals('retail_special_99k', $result['discount_type']);

        // ... Exception cases need new mocks as price changes ...
        
        $expensiveProduct = \Mockery::mock(Goods::class)->makePartial();
        $expensiveProduct->shouldReceive('getAttribute')->with('price')->andReturn(100000);
        $expensiveProduct->shouldReceive('getAttribute')->with('consumer_price')->andReturn(120000);
        
        $expensiveProduct->mtype_discount = 10000;
        $expensiveProduct->fifty_discount_ea = 0;
        $expensiveProduct->hundred_discount_ea = 0;

        // Case C: Exception - Unit Price > 99k AND Qty 1 -> Retail Price
        $result = $this->pricingService->calculatePrice($expensiveProduct, null, 1);
        $this->assertEquals(100000, $result['price']); // No discount
    
        // Case D: Exception Override - Unit Price > 99k AND Qty 2 -> Wholesale Price
        $result = $this->pricingService->calculatePrice($expensiveProduct, null, 2);
        $this->assertEquals(90000, $result['price']); // 100000 - 10000
    }
}
