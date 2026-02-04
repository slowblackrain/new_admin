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
        // Default to option price if available, else product price
        // Ensure we cast to float or int to avoid string subtraction issues
        $basePrice = (float) ($option ? $option->price : $product->price);

        // 1. Member Discount (mtype) calculation
        $mtypeDiscount = (float) ($product->mtype_discount ?? 0);

        // 2. Quantity (Tiered) Discount calculation
        // Legacy: fifty_price, hundred_price are explicitly stored in DB usually calculated from original price

        $fiftyEa = (int) ($product->fifty_discount_ea ?? 0);
        $hundredEa = (int) ($product->hundred_discount_ea ?? 0);

        $fiftyDiscount = (float) ($product->fifty_discount ?? 0);
        $hundredDiscount = (float) ($product->hundred_discount ?? 0);

        $targetPrice = $basePrice;
        $isDiscounted = false;
        $discountType = 'normal'; // normal, member, fifty, hundred

        // Determine Unit Price based on Quantity tiers
        if ($hundredEa > 0 && $quantity >= $hundredEa) {
            // Priority 1: 100+ items
            $targetPrice = $basePrice - $hundredDiscount;
            $discountType = 'hundred';
            $isDiscounted = true;
        } elseif ($fiftyEa > 0 && $quantity >= $fiftyEa) {
            // Priority 2: 50+ items
            $targetPrice = $basePrice - $fiftyDiscount;
            $discountType = 'fifty';
            $isDiscounted = true;
        } else {
            // Priority 3: Member Discount (Default for 1 item)
            $targetPrice = $basePrice - $mtypeDiscount;
            if ($mtypeDiscount > 0) {
                $discountType = 'member';
                $isDiscounted = true;
            }
        }

        return [
            // Legacy Compatibility Keys for view.blade.php
            'ori_price' => $basePrice,
            'price' => $targetPrice,

            // Standard Keys
            'original_price' => $basePrice,
            'unit_price' => $targetPrice,
            'discount_amount' => $basePrice - $targetPrice,
            'discount_type' => $discountType,
            'total_price' => $targetPrice * $quantity,
            'quantity' => $quantity,

            // Meta for UI (Legacy keys expected by view)
            'mtype_discount' => $mtypeDiscount,
            'fifty_ea' => $fiftyEa,
            'fifty_price' => $basePrice - $fiftyDiscount,
            'hundred_ea' => $hundredEa,
            'hundred_price' => $basePrice - $hundredDiscount,

            // Domain Terminology Mapping (User Defined)
            'somae_price' => $basePrice, // Retail Price (price)
            'domae_price' => $basePrice - $mtypeDiscount, // Wholesale Price (price - mtype_discount)
            'domae_discount_price' => $fiftyEa > 0 ? $basePrice - $fiftyDiscount : 0, // Wholesale Discount (price - fifty_discount)
            'suip_price' => $hundredEa > 0 ? $basePrice - $hundredDiscount : 0, // Import (price - hundred_discount)
            
            // Legacy keys for backward compatibility
            'fifty_price' => $basePrice - $fiftyDiscount,
            'hundred_price' => $basePrice - $hundredDiscount,
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
