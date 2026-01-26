/**
 * Dometopia Legacy Pricing Logic (Strict Implementation)
 * Based on legacy file: admin/skin/default/goods/regist.html (intupdate2)
 * 
 * Logic Summary:
 * 1. Base Price = Supply * (1 + Margin/100) or Supply * 1.1 (Default)
 * 2. Rounding (upCeil) = Math.round(v/10)*10
 * 3. Conditional Branches (A-F) based on Product Code (goodscd)
 * 
 * Author: Antigravity Agent
 * Date: 2026-01-26
 */

console.log('✅ Loaded: public/js/admin/goods_regist_pricing.js');

(function ($) {
    'use strict';

    // Legacy Rounding Function: upCeil
    function upCeil(v) {
        if (!v) return 0;
        return Math.round(v / 10) * 10;
    }

    // Main Calculator Function
    function calcTieredPricing() {
        console.log('🔄 calcTieredPricing Triggered');

        // 1. Get Inputs
        var supplyStr = $('#s_supply_price').val();
        if (!supplyStr) {
            console.log('⚠️ Supply Price is empty');
            return;
        }

        var supplyPrice = parseInt(supplyStr.replace(/,/g, ''));
        if (isNaN(supplyPrice) || supplyPrice <= 0) {
            console.log('⚠️ Supply Price is invalid:', supplyPrice);
            return;
        }

        // 2. Calculate Base Price
        var goodsRate = parseFloat($('#goods_rate').val()) || 0;
        var basePrice;

        if (goodsRate > 0) {
            basePrice = upCeil(supplyPrice * (1 + goodsRate / 100));
        } else {
            basePrice = upCeil(supplyPrice * 1.1); // Default 1.1 (10% Margin)
        }

        // 3. Identify Goods Code Branch
        // Check visible input first (user typing), then hidden (legacy/saved)
        var goodsCode = $('input[name="goodsScode"]').val() || $('#goodscd').val() || '';
        var prefix2 = goodsCode.substr(0, 2).toUpperCase();
        var prefix3 = goodsCode.substr(0, 3).toUpperCase();
        var suffix1 = goodsCode.substr(goodsCode.length - 1, 1).toUpperCase();

        var price50, price100;
        var qty50 = 50, qty100 = 100; // Defaults

        var branchName = 'F (Default)';

        if (prefix3 === 'ATQ' || prefix2 === 'B') {
            branchName = 'A (No Discount)';
            price50 = basePrice;
            price100 = basePrice;
            qty50 = 0;
            qty100 = 0;
        }
        else if (prefix3 === 'GDR') {
            branchName = 'B (GDR Retail)';
            price50 = basePrice;
            price100 = basePrice;
        }
        else if (prefix3 === 'GTH' || suffix1 === 'P') {
            branchName = 'C/D (Factory/P)';
            price50 = basePrice;
            price100 = basePrice;
            qty50 = 1;
            qty100 = 1;
        }
        else if (prefix2 === 'GT' || prefix2 === 'XT') {
            branchName = 'E (GT/XT High Discount)';
            price50 = upCeil(basePrice * 0.90);
            price100 = upCeil(basePrice * 0.80);

            if (price50 > 0) qty50 = parseInt(500000 / price50) + 1;
            if (price100 > 0) qty100 = parseInt(1000000 / price100) + 1;
        }
        else {
            // Case F (Default)
            branchName = 'F (Standard 95/90)';
            price50 = upCeil(basePrice * 0.95);
            price100 = upCeil(basePrice * 0.90);

            if (price50 > 0) qty50 = parseInt(500000 / price50) + 1;
            if (price100 > 0) qty100 = parseInt(1000000 / price100) + 1;
        }

        console.log('📊 Pricing Calc Result:', {
            supply: supplyPrice,
            rate: goodsRate,
            code: goodsCode,
            branch: branchName,
            base: basePrice,
            tier1: { p: price50, q: qty50 },
            tier2: { p: price100, q: qty100 }
        });

        // 4. Update UI
        $('input[name="fifty_discount"]').val(price50);
        $('input[name="fifty_discount_ea"]').val(qty50);

        $('input[name="hundred_discount"]').val(price100);
        $('input[name="hundred_discount_ea"]').val(qty100);
    }

    // Expose to window for inline calls if necessary (though we prefer jQuery binding)
    window.calcTieredPricing = calcTieredPricing;

    // Initialization
    $(document).ready(function () {
        console.log('🚀 Pricing Logic Initialized');

        // Bind Events
        $(document).on('keyup change', '#s_supply_price', function () {
            calcTieredPricing();
        });

        // Trigger when Product Code changes (to update Branch logic)
        $(document).on('keyup change', 'input[name="goodsScode"]', function () {
            calcTieredPricing();
        });

        $(document).on('keyup change', '#s_price', function () {
            // Optional secondary trigger if needed
            // calcTieredPricing(); 
        });

        // Run once on load if value exists
        if ($('#s_supply_price').val()) {
            calcTieredPricing();
        }
    });

})(jQuery);
