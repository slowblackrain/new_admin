<?php

namespace App\Services\Agency;

use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\GoodsImage;
use App\Models\CategoryLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AgencyProductService
{
    protected AgencyPriceCalculator $priceCalculator;

    public function __construct(AgencyPriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Duplicate a product for Agency Sales (ATS -> GT).
     *
     * @param int $sourceGoodsSeq
     * @param int $resellerMemberSeq
     * @return Goods
     * @throws Exception
     */
    public function duplicateProduct(int $sourceGoodsSeq, int $resellerMemberSeq): Goods
    {
        return DB::transaction(function () use ($sourceGoodsSeq, $resellerMemberSeq) {
            $sourceGoods = Goods::findOrFail($sourceGoodsSeq);

            // 1. Generate new SCODE with GT prefix
            $newScode = $this->generateGtScode($sourceGoods->goods_scode);

            // 2. Clone Goods Record
            $newGoods = $sourceGoods->replicate();
            $newGoods->goods_scode = $newScode;
            // Legacy consistency: goods_code (int) often mirrors seq or is just unique. 
            // We set it to a temp random int to avoid unique key collision on insert.
            $newGoods->goods_code = rand(10000000, 99999999); 
            
            $newGoods->goods_name = $sourceGoods->goods_name . '  가등록'; // Append temp tag
            $newGoods->goods_view = 'notLook'; // Hidden
            $newGoods->goods_status = 'unsold'; // Unsold initially
            $newGoods->provider_seq = $this->getProviderSeq($resellerMemberSeq); 
            $newGoods->regist_date = now();
            $newGoods->update_date = now();
            
            // Stock Reset (For Drop-shipping model: Set to unlimited or high number)
            $newGoods->tot_stock = 9999;
            $newGoods->runout_policy = 'unlimited'; // Unlimited stock policy
            
            // Remove Barcode (goods_contents2)
            $newGoods->goods_contents2 = '';

            $newGoods->save();

            // Fix goods_code (int) collision by setting it to goods_seq
            $newGoods->goods_code = $newGoods->goods_seq;
            $newGoods->save();

            // Link back to old goods for traceability
            DB::table('fm_goods')->where('goods_seq', $newGoods->goods_seq)->update(['old_goods_seq' => $sourceGoodsSeq]);


            // 3. Clone Options and Reset Stock
            $this->cloneOptions($sourceGoodsSeq, $newGoods->goods_seq);

            // 4. Clone Images
            $this->cloneImages($sourceGoodsSeq, $newGoods->goods_seq);

            // 5. Clone Categories
            $this->cloneCategories($sourceGoodsSeq, $newGoods->goods_seq);

            // 6. Suspend Source Product
            $this->suspendSourceProduct($sourceGoods);

            // 7. Create Offer (Incoming Logic Bypass)
            $this->createOfferRecord($newGoods->goods_seq);

            return $newGoods;
        });
    }

    protected function generateGtScode(string $oldScode): string
    {
        // Logic: Replace first 3 chars with GT if standard pattern
        // Or if it matches known prefixes.
        // Legacy: $N_goods_scode = "GT" . substr($oldScode, 3);
        // We should ensure we don't break short codes.
        if (strlen($oldScode) < 3) {
            return 'GT' . $oldScode;
        }
        return 'GT' . substr($oldScode, 3);
    }

    protected function getProviderSeq(int $memberSeq): int
    {
        // Resolve provider_seq from member_seq via fm_provider
        // Assuming 1:1 or 1:N but we take the first active one? 
        // In legacy, many resellers are providers.
        $provider = DB::table('fm_provider')->where('userid', function($query) use ($memberSeq) {
            $query->select('userid')->from('fm_member')->where('member_seq', $memberSeq);
        })->first();

        return $provider ? $provider->provider_seq : 0; // Default to 0 or handle error
    }

    protected function cloneOptions(int $oldSeq, int $newSeq)
    {
        $options = GoodsOption::where('goods_seq', $oldSeq)->get();
        foreach ($options as $option) {
            $newOption = $option->replicate();
            $newOption->goods_seq = $newSeq;
            
            // Recalculate Supply Price
            // Fetch original supply price from fm_goods_supply
            $sourceSupply = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->first();
            $originalSupplyPrice = $sourceSupply ? $sourceSupply->supply_price : 0;

            // Legacy default is 10% margin addition
            $newOption->provider_price = $this->priceCalculator->calculateSupplyPrice($originalSupplyPrice);
            
            // Commission calculation might actally happen here or depends on scheme.
            // For parity, we set the initial prices.
            
            $newOption->save();

            // Handle Stock (fm_goods_supply)
            // Create new supply record with unlimited stock
            DB::table('fm_goods_supply')->insert([
                'goods_seq' => $newSeq,
                'option_seq' => $newOption->option_seq, 
                'supply_price' => $newOption->provider_price, // Sync supply price?
                'stock' => 9999,
                'badstock' => 0,
                'safe_stock' => 0,
                'total_stock' => 9999
            ]);
        }
    }

    protected function cloneImages(int $oldSeq, int $newSeq)
    {
        $images = GoodsImage::where('goods_seq', $oldSeq)->get();
        foreach ($images as $image) {
            $newImage = $image->replicate();
            $newImage->goods_seq = $newSeq;
            $newImage->save();
        }
    }

    protected function cloneCategories(int $oldSeq, int $newSeq)
    {
        $links = CategoryLink::where('goods_seq', $oldSeq)->get();
        foreach ($links as $link) {
            $newLink = $link->replicate();
            $newLink->goods_seq = $newSeq;
            $newLink->save();
        }
    }

    protected function suspendSourceProduct(Goods $goods)
    {
        $goods->goods_view = 'notLook';
        $goods->goods_status = 'unsold';
        $goods->save();
    }

    protected function createOfferRecord(int $goodsSeq)
    {
        // Bypass step 11 logic
        DB::table('fm_offer')->insert([
            'goods_seq' => $goodsSeq,
            'step' => 11, // Hand-inwarehousing/One-time warehousing
            'regist_date' => now(), 
            // Add other mandatory fields based on schema if needed
        ]);
    }
}
