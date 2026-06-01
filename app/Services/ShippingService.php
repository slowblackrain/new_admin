<?php

namespace App\Services;

use App\Models\Goods;

class ShippingService
{
    const FREE_SHIPPING_THRESHOLD = 150000; // 150,000 KRW

    /**
     * Calculate shipping cost for a single product with quantity.
     * Maps to legacy fm_goods column rules for Dropship / Goods policy.
     * 
     * @param Goods $product
     * @param int $ea
     * @return int
     */
    public function calculateProductShipping(Goods $product, int $ea = 1): int
    {
        // 1. Postpaid (착불) check
        if ($product->postpaid_delivery_cost_yn === 'y') {
            return 0; // 착불은 선결제 배송비 없음
        }

        // 2. Individual goods policy check
        if ($product->shipping_policy === 'goods') {
            if ($product->goods_shipping_policy === 'unlimit') {
                return (int) ($product->unlimit_shipping_price ?? 0);
            }
            if ($product->goods_shipping_policy === 'limit') {
                $limitEa = (int) ($product->limit_shipping_ea ?? 1);
                if ($limitEa <= 0) $limitEa = 1;
                $limitPrice = (int) ($product->limit_shipping_price ?? 0);
                
                return (int) (ceil($ea / $limitEa) * $limitPrice);
            }
        }

        // 3. Fallback to basic shop shipping cost
        return 2500;
    }

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
        $shippingMethod = $product->shipping_method ?? 'prepay';
        
        // Use individual rules if policy is 'goods'
        if ($product->shipping_policy === 'goods') {
            $shippingPrice = $this->calculateProductShipping($product, 1);
            if ($product->postpaid_delivery_cost_yn === 'y') {
                $shippingMethod = 'postpaid';
            }
        } else {
            $shippingPrice = 2500; // Default fallback
        }

        if ($shippingMethod == 'free') {
            $shippingPrice = 0;
        }

        // Check Threshold (Only applicable to standard Shop shipping policy, dropship usually has no free threshold unless specified)
        $finalShippingCost = $shippingPrice;
        $isFree = false;
        
        if ($product->shipping_policy !== 'goods') {
            if ($totalAmount >= self::FREE_SHIPPING_THRESHOLD) {
                $finalShippingCost = 0;
                $isFree = true;
            } else {
                $isFree = false;
            }
        } else {
            // Dropship goods shipping cost is absolute per items, but if cost is 0 treat as free
            if ($shippingPrice === 0) {
                $isFree = true;
            }
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
