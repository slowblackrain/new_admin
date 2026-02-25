<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\GoodsSupply;
use App\Models\GoodsImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtsReplicationService
{
    /**
     * Replicates an ATS (Pairing B2B) product into an Agency's GT product.
     * Port of legacy `goodsmodel::biz_goods_proc_admin`
     */
    public function replicateAtsToAgency($goodsSeq, $agencyMemberSeq)
    {
        try {
            DB::beginTransaction();
            
            // 1. Mark original ATS product as unsold/hidden
            Goods::where('goods_seq', $goodsSeq)->update([
                'goods_status' => 'unsold',
                'goods_view' => 'notLook'
            ]);

            $oldGoods = Goods::find($goodsSeq);
            if (!$oldGoods) {
                throw new \Exception("ATS Original Goods Not Found: {$goodsSeq}");
            }
            
            $oldScode = $oldGoods->goods_scode;

            // 2. Deep Copy Goods Data
            // Eloquent replication or DB::raw array copy
            $newGoodsAttrs = $oldGoods->toArray();
            unset($newGoodsAttrs['goods_seq']); // Auto-increment

            $newScode = "FFF" . substr($oldScode, 3);
            $newMemo = "\n" . now()->format('Y-m-d H:i:s') . " " . $oldScode . " 판매대행상품으로 개발되었음.";

            // Resetting specific properties based on legacy
            $newGoodsAttrs['goods_scode'] = $newScode;
            $newGoodsAttrs['admin_memo'] = ($newGoodsAttrs['admin_memo'] ?? '') . $newMemo;
            $newGoodsAttrs['old_goods_seq'] = $goodsSeq;
            $newGoodsAttrs['provider_member_seq'] = $agencyMemberSeq;
            $newGoodsAttrs['min_purchase_limit'] = 'unlimit';
            $newGoodsAttrs['min_purchase_ea'] = 0;
            $newGoodsAttrs['tot_stock'] = 0;
            $newGoodsAttrs['goods_status'] = 'normal'; // Usually new products start normal or wait admin approval. Legacy left it alone from copy except the min limits.

            // Insert New Goods
            $newGoodsSeq = DB::table('fm_goods')->insertGetId($newGoodsAttrs);

            // 3. Copy Linked Tables (Category, Brand, Image, Option, Supply)
            $this->copyLinkedTables('fm_category_link', $goodsSeq, $newGoodsSeq, 'category_link_seq');
            $this->copyLinkedTables('fm_brand_link', $goodsSeq, $newGoodsSeq, 'category_link_seq');
            $this->copyLinkedTables('fm_goods_addition', $goodsSeq, $newGoodsSeq, 'addition_seq');
            $this->copyLinkedTables('fm_goods_icon', $goodsSeq, $newGoodsSeq, 'icon_seq');
            $this->copyLinkedTables('fm_goods_input', $goodsSeq, $newGoodsSeq, 'input_seq');
            $this->copyLinkedTables('fm_goods_relation', $goodsSeq, $newGoodsSeq, 'relation_seq');
            $this->copyLinkedTables('fm_location_link', $goodsSeq, $newGoodsSeq, 'location_link_seq');
            $this->copyLinkedTables('fm_goods_socialcp_cancel', $goodsSeq, $newGoodsSeq, 'seq');
            
            // Image replication
            $this->copyLinkedTables('fm_goods_image', $goodsSeq, $newGoodsSeq, 'image_seq');

            // 4. Coping Options and Supplies
            $this->copyOptionsAndSupplies($goodsSeq, $newGoodsSeq);

            // 5. Delete specific "Sample Order" suboption because FFF products don't allow it
            $sampleSuboption = DB::table('fm_goods_suboption')
                ->where('goods_seq', $newGoodsSeq)
                ->where('suboption_title', '샘플구매')
                ->first();
            
            if ($sampleSuboption) {
                GoodsSupply::where('goods_seq', $newGoodsSeq)->where('suboption_seq', $sampleSuboption->suboption_seq)->delete();
                DB::table('fm_goods_suboption')->where('suboption_seq', $sampleSuboption->suboption_seq)->delete();
            }

            // 6. Force Supply Stocks to 0
            GoodsSupply::where('goods_seq', $newGoodsSeq)->update([
                'stock' => 0,
                'reservation15' => 0,
                'reservation25' => 0,
                'total_stock' => 0
            ]);

            // 7. Mark original ATS out of stock
            $runoutMemo = "\n" . now()->format('Y-m-d H:i:s') . " " . $newScode . " 판매대행상품 등록으로 인한 품절이 되었습니다.";
            $oldGoods->update([
                'runout_date' => now(),
                'admin_memo' => DB::raw("CONCAT(COALESCE(admin_memo, ''), '{$runoutMemo}')")
            ]);

            DB::commit();
            
            Log::info("ATS Automated Replication Passed. Original: {$goodsSeq} (Agency: {$agencyMemberSeq}). New: {$newGoodsSeq}");
            return $newGoodsSeq;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ATS Replication Failed: " . $e->getMessage());
            return false;
        }
    }

    private function copyLinkedTables($tableName, $oldGoodsSeq, $newGoodsSeq, $primaryKey, $overrideData = [])
    {
        $records = DB::table($tableName)->where('goods_seq', $oldGoodsSeq)->get()->map(function ($item) {
            return (array) $item;
        })->toArray();

        foreach ($records as $record) {
            unset($record[$primaryKey]); // drop PK
            $record['goods_seq'] = $newGoodsSeq;
            
            foreach ($overrideData as $k => $v) {
                $record[$k] = $v;
            }

            DB::table($tableName)->insert($record);
        }
    }

    private function copyOptionsAndSupplies($oldGoodsSeq, $newGoodsSeq)
    {
        // Copy Options
        $options = GoodsOption::where('goods_seq', $oldGoodsSeq)->get();
        $optionMap = []; // old_seq => new_seq

        foreach ($options as $opt) {
            $optArray = $opt->toArray();
            $oldOptSeq = $optArray['option_seq'];
            unset($optArray['option_seq']);
            $optArray['goods_seq'] = $newGoodsSeq;
            
            $newOptSeq = DB::table('fm_goods_option')->insertGetId($optArray);
            $optionMap[$oldOptSeq] = $newOptSeq;
            
            // Also copy supplies mapping to this option
            $supplies = GoodsSupply::where('goods_seq', $oldGoodsSeq)->where('option_seq', $oldOptSeq)->get();
            foreach ($supplies as $sup) {
                $supArray = $sup->toArray();
                unset($supArray['supply_seq']);
                $supArray['goods_seq'] = $newGoodsSeq;
                $supArray['option_seq'] = $newOptSeq;
                DB::table('fm_goods_supply')->insert($supArray);
            }
        }

        // Copy Suboptions
        $suboptions = DB::table('fm_goods_suboption')->where('goods_seq', $oldGoodsSeq)->get();
        $suboptionMap = []; // old_seq => new_seq

        foreach ($suboptions as $sub) {
            $subArray = (array) $sub;
            $oldSubSeq = $subArray['suboption_seq'];
            unset($subArray['suboption_seq']);
            $subArray['goods_seq'] = $newGoodsSeq;
            
            $newSubSeq = DB::table('fm_goods_suboption')->insertGetId($subArray);
            $suboptionMap[$oldSubSeq] = $newSubSeq;
            
            // Copy supplies mapping to this suboption
            $subSupplies = DB::table('fm_goods_supply')->where('goods_seq', $oldGoodsSeq)->where('suboption_seq', $oldSubSeq)->get();
            foreach ($subSupplies as $sup) {
                $supArray = (array) $sup;
                unset($supArray['supply_seq']);
                $supArray['goods_seq'] = $newGoodsSeq;
                $supArray['suboption_seq'] = $newSubSeq;
                DB::table('fm_goods_supply')->insert($supArray);
            }
        }
    }
}
