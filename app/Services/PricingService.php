<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\GoodsOption;
use Illuminate\Support\Facades\Auth;

class PricingService
{
    /**
     * Calculate the price based on quantity and user group.
     *
     * @param Goods $product
     * @param GoodsOption|null $option
     * @param int $quantity
     * @return array Price information structure
     */
    public function calculatePrice(Goods $product, ?GoodsOption $option, int $quantity = 1): array
    {
        // 0. Base Price & User Info
        $user = Auth::user();
        $memberSeq = $user ? $user->member_seq : null;
        $mtype = $user ? $user->mtype : null;
        $groupSeq = $user ? $user->group_seq : null;

        $basePrice = (float) ($option ? $option->price : $product->price);
        $consumerPrice = (float) ($option ? $option->consumer_price : $product->consumer_price);

        // 1. Discount Data
        $mtypeDiscount = (float) ($product->mtype_discount ?? 0);
        $fiftyEa = (int) ($product->fifty_discount_ea ?? 0); // Tier 1 Threshold
        $hundredEa = (int) ($product->hundred_discount_ea ?? 0); // Tier 2 Threshold
        $fiftyDiscount = (float) ($product->fifty_discount ?? 0); // Tier 1 Discount
        $hundredDiscount = (float) ($product->hundred_discount ?? 0); // Tier 2 Discount

        // 2. Determine Pricing Tier
        $targetPrice = $basePrice; 
        $discountType = 'retail';
        $isDiscounted = false;

        // A. Staff Pricing (Group 2)
        if ($groupSeq == 2) {
            $cbmString = $product->multi_discount_cbm;
            $scode = $product->goods_scode;

            if ($cbmString) {
                $cbm = explode('|', $cbmString);
                $prefix2 = substr($scode, 0, 2);
                $prefix3 = substr($scode, 0, 3);

                // Logic based on gubun_helper.php
                if ($prefix2 == 'GK' || $prefix3 == 'GDF') {
                    // GK, GDF: Price = cbm[6] + cbm[16]
                    $targetPrice = (float)($cbm[6] ?? $basePrice);
                    if (isset($cbm[16]) && $cbm[16] > 0) {
                        $targetPrice += (float)$cbm[16];
                    }
                    $discountType = 'staff_special';
                    $isDiscounted = true;
                    // Skip other logic
                    goto finalize_calculation; 

                } elseif ($prefix2 == 'GT' || $prefix2 == 'XT' || $prefix3 == 'MXT') {
                    if (!in_array($prefix3, ['GTH', 'GTP', 'GTQ'])) {
                         // GT, XT, MXT (except GTH, GTP, GTQ): Price = round(cbm[6] * 0.8, -1) + cbm[16]
                        $baseCbmPrice = (float)($cbm[6] ?? $basePrice);
                        $targetPrice = round($baseCbmPrice * 0.8, -1);
                        
                        if (isset($cbm[16]) && $cbm[16] > 0) {
                            $targetPrice += (float)$cbm[16];
                        }
                        $discountType = 'staff_special';
                        $isDiscounted = true;
                        // Skip other logic
                        goto finalize_calculation;
                    }
                }
            }
            // Fallback for Staff -> Treat as Business/Wholesale if not captured above
        }

        // B. Volume Discounts (Dynamic Thresholds)
        // Check Tier 2 (Largest Discount) first
        if ($hundredEa > 0 && $quantity >= $hundredEa) {
            $targetPrice = $basePrice - $hundredDiscount;
            $discountType = 'volume_tier_2'; // "Import/Factory" price
            $isDiscounted = true;
        } 
        // Check Tier 1
        elseif ($fiftyEa > 0 && $quantity >= $fiftyEa) { // Priority Adjust: Must be checked after Tier 2
             if ($hundredEa > 0 && $quantity >= $hundredEa) {
                 // Already handled above, ensuring mutually exclusive logic if needed
             } else {
                $targetPrice = $basePrice - $fiftyDiscount;
                $discountType = 'volume_tier_1'; // "Wholesale Discount" price
                $isDiscounted = true;
             }
        } 
        // C. Wholesale / Member Discount
        else {
            // Apply Wholesale Price (mtype_discount) IF:
            // 1. User is Business Member OR Staff (Group 2 - fallback)
            // 2. OR Line Item Total (> 99,999 KRW) Rule [Legacy: if($tmp > 99999)]
            
            $lineTotal = $basePrice * $quantity;
            $isBusiness = ($mtype === 'business' || $groupSeq == 2);
            
            $isRetailSpecial = false;
            if (!$isBusiness && $lineTotal > 99999) {
                 // Exception: If Unit Price > 99,999 AND Quantity == 1, DO NOT apply discount
                 if ($basePrice > 99999 && $quantity == 1) {
                     $isRetailSpecial = false;
                 } else {
                     $isRetailSpecial = true;
                 }
            }

            if ($isBusiness || $isRetailSpecial) {
                if ($mtypeDiscount > 0) {
                    $targetPrice = $basePrice - $mtypeDiscount;
                    $discountType = $isBusiness ? 'wholesale' : 'retail_special_99k';
                    $isDiscounted = true;
                }
            } else {
                // Retail Price (No discount)
                $targetPrice = $basePrice;
                $discountType = 'retail';
                $isDiscounted = false;
            }
        }

        finalize_calculation:

        // Ensure price doesn't go negative
        if ($targetPrice < 0) $targetPrice = 0;

        return [
            // Standard Keys
            'ori_price' => $basePrice,
            'price' => $targetPrice,

            'original_price' => $basePrice,
            'unit_price' => $targetPrice,
            'discount_amount' => $basePrice - $targetPrice,
            'discount_type' => $discountType,
            'total_price' => $targetPrice * $quantity,
            'quantity' => $quantity,

            // Meta for UI
            'mtype_discount' => $mtypeDiscount,
            'fifty_ea' => $fiftyEa,
            'fifty_price' => $basePrice - $fiftyDiscount,
            'hundred_ea' => $hundredEa,
            'hundred_price' => $basePrice - $hundredDiscount,

            // Legacy Domain Terminology Mapping for Compatibility
            'somae_price' => $basePrice, 
            'domae_price' => $basePrice - $mtypeDiscount, 
            'domae_discount_price' => $fiftyEa > 0 ? $basePrice - $fiftyDiscount : 0, 
            'suip_price' => $hundredEa > 0 ? $basePrice - $hundredDiscount : 0, 
        ];
    }

    /**
     * Parse pricing info for view display (View page mainly)
     */
    public function getProductPricingInfo(Goods $product)
    {
        // Default to first option or dummy for display
        $firstOption = $product->option->first();
        return $this->calculatePrice($product, $firstOption, 1);
    }
}
