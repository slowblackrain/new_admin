<?php

namespace Tests\Unit\Services\Goods;

use App\Services\Goods\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    protected $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PriceCalculator();
    }

    public function test_calculate_cost()
    {
        // Cost = RealCost + Others
        $real = 10000;
        $others = ['mem' => 100, 'sticker' => 0, 'package' => 500, 'delivery' => 0, 'etc' => 0];
        
        $result = $this->calculator->calculateCost($real, $others);
        
        // 10000 + 100 + 500 = 10600
        $this->assertEquals(10600.0, $result);
    }

    public function test_calculate_landed_price_standard()
    {
        // Formula: (Price + Transport + Duty) * Incidental * 1.05 Overhead
        // Price = Cost * Exchange
        
        $baseCostKrw = 10000; // After exchange
        $cbmUnit = 0.01;
        $transportRate = 100000; // Transport Cost Per CBM? No, formulas was `transport * xyh_ea`
        // Wait, legacy JS: `transport` was input value. `xyh_ea` was CBM.
        // My service parameter: $transportRate (which is the Transport Cost Factor, effectively 'transport' input)
        
        $transportCostInput = 50000; // e.g. 50,000 KRW per CBM? Use reasonable number
        
        $dutyRate = 0.08; // 8%
        $incidentalRate = 1.0;
        
        // Calculation:
        // Transport = 50000 * 0.01 = 500
        // Duty = 10000 * 0.08 = 800
        // Base Landed = 10000 + 500 + 800 = 11300
        // Incidental = 11300 * 1.0 = 11300
        // Overhead = 11300 * 1.05 = 11865
        // UpCeil (Round to nearest 10) -> 11870? Or 11860?
        // PHP round(11865, -1) -> 11870.
        
        $result = $this->calculator->calculateLandedPrice($baseCostKrw, $cbmUnit, $transportCostInput, $dutyRate, $incidentalRate);
        
        $this->assertEquals(11870.0, $result);
    }

    public function test_calculate_wholesale_price()
    {
        $landed = 10000;
        // Default margin 1.1
        // 10000 * 1.1 = 11000
        $result = $this->calculator->calculateWholesalePrice($landed);
        $this->assertEquals(11000.0, $result);
        
        // Custom margin
        $resultCustom = $this->calculator->calculateWholesalePrice($landed, 1.2);
        // 12000
        $this->assertEquals(12000.0, $resultCustom);
    }

    public function test_calculate_retail_price_ranges()
    {
        // < 100,000 -> 1.5 multiplier
        $wholesale1 = 10000; 
        $result1 = $this->calculator->calculateRetailPrice($wholesale1);
        $this->assertEquals(15000.0, $result1); // 10000 * 1.5

        // 100,000 ~ 200,000 -> 1.45
        $wholesale2 = 100000;
        $result2 = $this->calculator->calculateRetailPrice($wholesale2);
        // 100000 * 1.45 = 145000
        $this->assertEquals(145000.0, $result2);

        // >= 200,000 -> 1.37
        $wholesale3 = 200000;
        $result3 = $this->calculator->calculateRetailPrice($wholesale3);
        // 200000 * 1.37 = 274000
        $this->assertEquals(274000.0, $result3);
    }
    
    public function test_calculate_cbm()
    {
        // 100cm x 50cm x 20cm / 2 pcs
        // 1.0 * 0.5 * 0.2 = 0.1
        // 0.1 / 2 = 0.05
        
        $result = $this->calculator->calculateCbmUnit(100, 50, 20, 2);
        $this->assertEquals(0.05, $result);
    }
}
