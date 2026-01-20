<?php

namespace App\Services;

use App\Models\Goods;

class ShippingService
{
    const FREE_SHIPPING_THRESHOLD = 150000; // 150,000 KRW

    /**
     * Calculate shipping cost for a set of items (Cart or Layout)
     * For single product view, we calculate estimated shipping.
     * 
     * @param Goods $product
     * @param int $totalAmount
     * @return array
     */
    public function calculateShipping(Goods $product, int $totalAmount = 0): array
    {
        // Provider shipping policy (To be fully implemented with Provider model relationship)
        // For now, using basic logic based on fm_goods columns if available or defaults

        $shippingMethod = $product->shipping_method ?? 'prepay'; // basic, free, etc.
        $shippingPrice = 2500; // Default fallback
        // Check if provider has specific shipping logic (legacy: fm_provider)
        // Assuming we might fetch this from $product->provider later

        // Temp logic matching typical Dometopia behavior
        if ($shippingMethod == 'free') {
            $shippingPrice = 0;
        }

        // Check Threshold
        if ($totalAmount >= self::FREE_SHIPPING_THRESHOLD) {
            $finalShippingCost = 0;
            $isFree = true;
        } else {
            $finalShippingCost = $shippingPrice;
            $isFree = false;
        }

        return [
            'method' => $shippingMethod,
            'base_cost' => $shippingPrice,
            'cost' => $finalShippingCost,
            'is_free' => $isFree,
            'threshold' => self::FREE_SHIPPING_THRESHOLD,
            'remains_for_free' => max(0, self::FREE_SHIPPING_THRESHOLD - $totalAmount)
        ];
    }
}
