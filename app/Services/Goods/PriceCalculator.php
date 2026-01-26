<?php

namespace App\Services\Goods;

class PriceCalculator
{
    /**
     * Calculate Base Cost (기준 원가)
     * Corresponds to `Cost_Calc` in JS.
     *
     * @param float $realCost 실원가
     * @param array $otherCosts 기타 비용들 ['mem', 'sticker', 'package', 'delivery', 'etc']
     * @return float
     */
    public function calculateCost(float $realCost, array $otherCosts): float
    {
        $totalOtherCost = 0;
        foreach ($otherCosts as $cost) {
            $totalOtherCost += (float)$cost;
        }

        // Round to 2 decimal places as per legacy `round(costPrice, 2)`
        return round($realCost + $totalOtherCost, 2);
    }

    /**
     * Calculate Landed Price (수입 원가)
     * Corresponds to `SUBCBMWAY` / `CBMSUM` in JS.
     *
     * @param float $baseCostKrw 원가 (KRW converted: costPrice * exchangeRate)
     * @param float $cbmUnit CBM per Unit (Calculated from dimensions)
     * @param float $transportRate 운송비 Rate
     * @param float $dutyRate 관세 Rate (0.0 ~ 1.0)
     * @param float $incidentalRate 부대비용 Rate (Multiplier, e.g., 1.05)
     * @return float
     */
    public function calculateLandedPrice(float $baseCostKrw, float $cbmUnit, float $transportRate, float $dutyRate, float $incidentalRate): float
    {
        // Formula: (Price + (transport * xyh_ea) + (Price * customs)) * incidental
        // incidental usually includes margin or markup in legacy logic context, often passed as directly 'incidental' value input
        
        $transportCost = $transportRate * $cbmUnit;
        $dutyCost = $baseCostKrw * $dutyRate;
        
        $landedPrice = ($baseCostKrw + $transportCost + $dutyCost) * $incidentalRate;

        // Legacy: document.getElementsByName('realPriceIn')[0].value = upCeil(document.getElementsByName('realPriceIn')[0].value * 1.05)
        // Applying the mandatory 5% overhead margin logic found in legacy
        $landedPriceWithOverhead = $landedPrice * 1.05;

        return $this->upCeil($landedPriceWithOverhead);
    }

     /**
     * Calculate CBM per Unit
     * 
     * @param float $w Width (cm)
     * @param float $d Depth (cm)
     * @param float $h Height (cm)
     * @param int $quantityBox Quantity per Box
     * @return float
     */
    public function calculateCbmUnit(float $w, float $d, float $h, int $quantityBox): float
    {
        if ($quantityBox <= 0) {
            return 0.0;
        }

        // Convert cm to m for CBM calculation: (w/100 * d/100 * h/100) / quantity
        $transW = $w / 100;
        $transD = $d / 100;
        $transH = $h / 100;

        return ($transW * $transD * $transH) / $quantityBox;
    }

    /**
     * Calculate Wholesale Price (도매가)
     * Corresponds to `intupdate` logic where mtypeDiscount is calculated.
     *
     * @param float $landedPrice 수입 원가 (realPriceIn)
     * @param float $marginRate 마진율 (Default 1.1)
     * @return float
     */
    public function calculateWholesalePrice(float $landedPrice, float $marginRate = 1.1): float
    {
        // Legacy default logic: provider_price * 1.1
        // Using upCeil as per legacy
        return $this->upCeil($landedPrice * $marginRate);
    }

    /**
     * Calculate Retail Price (소비자가 - 권장판매가)
     * Corresponds to `price` calculation based on `mtypeDiscount`.
     *
     * @param float $wholesalePrice 도매가
     * @return float
     */
    public function calculateRetailPrice(float $wholesalePrice): float
    {
        /*
         * Legacy Logic:
         * < 50,000: 1.5
         * 50,000 ~ 99,999: 1.5
         * 100,000 ~ 199,999: 1.45
         * >= 200,000: 1.37
         */
        
        if ($wholesalePrice < 100000) {
            $multiplier = 1.5;
        } elseif ($wholesalePrice < 200000) {
            $multiplier = 1.45;
        } else {
            $multiplier = 1.37;
        }

        return $this->upCeil($wholesalePrice * $multiplier);
    }

    /**
     * Legacy `upCeil` function port.
     * 1자리수 올림함수 (Rounds up to nearest 10).
     * e.g., 123 -> 130, 125 -> 130 ?? 
     * 
     * JS Logic:
     * var val = String(Math.round(Number(v)));
     * return (Number(val.substr(val.length-1, val.length)) > 4) ? Number(val.substr(0, val.length - 1))+1+"0" : val.substr(0, val.length - 1)+"0";
     * 
     * If last digit > 4, round up next digit (effectively round half up to nearest 10)
     * Wait, JS Logic analysis:
     * 123 -> last digit 3. 3 !> 4. returns 120. (Floor to 10)
     * 126 -> last digit 6. 6 > 4. returns 12+1 + "0" = 130. (Ceil to 10)
     * Basically standard Round to nearest 10?
     * Math.round(123/10)*10 = 120
     * Math.round(126/10)*10 = 130
     * Math.round(125/10)*10 = 130
     * 
     * Let's test JS logic for 125. 
     * val = "125". last="5". 5>4 is true. 12+1 = 13. "130".
     * Yes, it is effectively `round($v, -1)`.
     */
    private function upCeil(float $value): float
    {
        // Using PHP's round with negative precision for nearest 10
        return round($value, -1);
    }
}
