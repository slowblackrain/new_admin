<?php

namespace App\Services\Agency;

class AgencyPriceCalculator
{
    /**
     * Calculate the Agency Supply Price based on the original supply price and provider state (margin rate).
     *
     * Logic matches legacy `jhmodel.php`:
     * Round((Original_Supply_Price * (1 + Provider_State/100)), -1)
     * Default Provider State is 10 (1.1 multiplier) if not provided.
     *
     * @param float $originalSupplyPrice
     * @param float|null $providerState e.g., 10 for 10% margin (1.1 total multiplier)
     * @return float
     */
    public function calculateSupplyPrice(float $originalSupplyPrice, ?float $providerState = null): float
    {
        // Default to 10% margin if not set (Legacy default)
        $marginRate = $providerState ?? 10.0;
        
        $multiplier = 1 + ($marginRate / 100);
        
        $calculatedPrice = $originalSupplyPrice * $multiplier;

        // Round to nearest 10
        return round($calculatedPrice, -1);
    }

     /**
     * Calculate Agency Order Price (Purchase Amount).
     *
     * If a specific provider_price is set, it uses that.
     * Otherwise, it defaults to: ROUND(ROUND((Original_Selling_Price - Discount) * 0.95, -1) * 1.1, -1)
     *
     * @param float $originalSellingPrice
     * @param float $discount
     * @return float
     */
    public function calculateDefaultOrderPrice(float $originalSellingPrice, float $discount = 0): float
    {
        // Step 1: Apply 5% discount to selling price (0.95) and round to nearest 10
        $base = round(($originalSellingPrice - $discount) * 0.95, -1);

        // Step 2: Apply 10% margin (1.1) and round to nearest 10
        return round($base * 1.1, -1);
    }
}
