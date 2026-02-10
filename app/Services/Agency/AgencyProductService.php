<?php

namespace App\Services\Agency;

use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\GoodsSupply;
use App\Models\GoodsImage;
use App\Models\CategoryLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgencyProductService
{
    /**
     * Process an agency order (ATS purchase) by copying the product for the reseller.
     *
     * @param int $orderSeq
     * @return void
     */
    public function processAgencyOrder($orderSeq)
    {
        // 1. Find order items that are potentially ATS products
        // We look for items where the product hasn't been copied yet (old_goods_seq check on fm_goods is the indicator)
        
        $orderItems = DB::table('fm_order_item as i')
            ->join('fm_order as o', 'i.order_seq', '=', 'o.order_seq')
            ->select('i.goods_seq', 'o.member_seq', 'i.item_seq')
            ->where('i.order_seq', $orderSeq)
            ->get();

        foreach ($orderItems as $item) {
            // Check if this product has already been copied
            $exists = Goods::where('old_goods_seq', $item->goods_seq)->exists();

            if (!$exists) {
                // Determine if it's an ATS product (optional check, but good for safety)
                // For now, assuming standard flow where only ATS products hit this logic via trigger
                $this->duplicateProduct($item->goods_seq, $item->member_seq);
            }
        }
    }

    /**
     * Copy an ATS product to a GTS product (Single Unit) for a specific reseller.
     *
     * @param int $originalGoodsSeq
     * @param int $resellerMemberSeq
     * @return int New Goods Sequence
     */
    public function duplicateProduct($originalGoodsSeq, $resellerMemberSeq)
    {
        return DB::transaction(function () use ($originalGoodsSeq, $resellerMemberSeq) {
            $original = Goods::findOrFail($originalGoodsSeq);

            // 1. Duplicate Main Goods
            $newGoods = $original->replicate();
            $newGoods->regist_date = now();
            $newGoods->update_date = now();
            
            // Handle Unique goods_code
            // goods_code is int(10)
            // Use time() as a temporary unique integer
            $newGoods->goods_code = time();

            // Legacy logic for 'FFF' prefix
            // $N_goods_scode = "FFF".substr($oldScode, 3);
            if (Str::startsWith($original->goods_scode, 'FFF')) {
                 $newGoods->goods_scode = $original->goods_scode . '_COPY'; // Prevent infinite FFF loop if re-copying?
            } else {
                 $newGoods->goods_scode = 'FFF' . substr($original->goods_scode, 3);
            }
            
            $newGoods->provider_member_seq = $resellerMemberSeq;
            $newGoods->old_goods_seq = $originalGoodsSeq;
            
            // Force Stock to 0 (Admin must confirm quantity later)
            $newGoods->min_purchase_limit = 'unlimit';
            $newGoods->min_purchase_ea = 0;
            $newGoods->tot_stock = 0;
            
            // Admin Memo
            $newGoods->admin_memo = "\n" . now() . " " . $original->goods_scode . " 판매대행상품으로 개발되었음.";
            
            $newGoods->save();
            $newGoodsSeq = $newGoods->goods_seq;

            // 2. Duplicate Related Data
            $this->copyRelatedData($originalGoodsSeq, $newGoodsSeq);

            // 3. Duplicate Options & Supply (Crucial for Stock)
            $this->copyOptions($originalGoodsSeq, $newGoodsSeq);

            // 4. Update Original Product (Mark as Sold Out/Unsold)
            // Legacy: update fm_goods set goods_status = 'unsold', goods_view='notLook' ...
            $original->goods_status = 'unsold';
            $original->goods_view = 'notLook';
            $original->runout_date = now();
            // Append memo
            $original->admin_memo .= "\n" . now() . " " . $newGoods->goods_scode . " 판매대행상품 등록으로 인한 품절이 되었습니다.";
            $original->save();

            return $newGoodsSeq;
        });
    }

    protected function copyRelatedData($oldSeq, $newSeq)
    {
        // fm_category_link
        $catLinks = CategoryLink::where('goods_seq', $oldSeq)->get();
        foreach ($catLinks as $link) {
            $newLink = $link->replicate();
            $newLink->goods_seq = $newSeq;
            $newLink->save();
        }

        // fm_goods_image
        $images = GoodsImage::where('goods_seq', $oldSeq)->get();
        foreach ($images as $img) {
            $newImg = $img->replicate();
            $newImg->goods_seq = $newSeq;
            $newImg->save();
        }
    }

    protected function copyOptions($oldSeq, $newSeq)
    {
        $options = GoodsOption::where('goods_seq', $oldSeq)->get();
        foreach ($options as $opt) {
            $newOpt = $opt->replicate();
            $newOpt->goods_seq = $newSeq;
            $newOpt->save();
            
            $newOptSeq = $newOpt->option_seq; // Assuming auto-increment

            // Copy Supply (Stock)
            // Note: Option duplication usually implies new option_seq is generated.
            // We need to fetch supply related to OLD option_seq and create new one for NEW option_seq.
            
            $supplies = GoodsSupply::where('goods_seq', $oldSeq)->where('option_seq', $opt->option_seq)->get();
            foreach ($supplies as $supply) {
                // Replicate supply logic
                $newSupply = $supply->replicate();
                $newSupply->goods_seq = $newSeq;
                $newSupply->option_seq = $newOptSeq; // Link to new option
                
                // Force Stock 0
                $newSupply->stock = 0;
                $newSupply->reservation15 = 0;
                $newSupply->reservation25 = 0;
                $newSupply->total_stock = 0;
                
                $newSupply->save();
            }
        }
        
        // Remove 'Sample Purchase' option if exists logic
        // For strict parity:
        // DB::table('fm_goods_suboption')->where('goods_seq', $newSeq)->where('suboption_title', '샘플구매')->delete();
    }
}
