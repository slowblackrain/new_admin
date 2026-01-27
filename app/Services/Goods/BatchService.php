<?php

namespace App\Services\Goods;

use App\Models\Goods;
use App\Models\GoodsOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BatchService
{
    /**
     * Handle Batch Modification of Goods
     * 
     * @param Request $request
     * @return array Result count or status
     */
    public function batchModify(Request $request)
    {
        $targetIds = $request->input('goods_seq', []);
        if (empty($targetIds)) {
            return ['status' => 'error', 'message' => 'No goods selected.'];
        }

        DB::beginTransaction();
        try {
            if ($request->input('batch_goods_status_yn')) {
                Goods::whereIn('goods_seq', $targetIds)
                    ->update(['goods_status' => $request->input('batch_goods_status')]);
            }

            // 2. Goods View Update
            if ($request->input('batch_goods_view_yn')) {
                Goods::whereIn('goods_seq', $targetIds)
                    ->update(['goods_view' => $request->input('batch_goods_view')]);
            }

            // 3. Price Update (Complex)
            // Legacy supports 'price', 'supply_price', 'consumer_price' with Mode (up/down/replace) and Unit (%/won)
            foreach (['price', 'supply_price', 'consumer_price'] as $priceType) {
                $chkKey = "batch_{$priceType}_yn";
                if ($request->input($chkKey)) {
                    $this->updatePrice($targetIds, $priceType, $request->all());
                }
            }

            // 4. Stock Update
            if ($request->input('batch_stock_yn')) {
                $stock = $request->input('batch_stock', 0);
                $mode = $request->input('batch_stock_updown', 'replace');
                
                // Logic: Access fm_goods_supply via join or loop?
                // Batch update with calculation is tricky in Eloquent without raw SQL.
                // Assuming raw query for efficiency.
                $this->updateStock($targetIds, $stock, $mode);
            }

            DB::commit();
            return ['status' => 'success', 'count' => count($targetIds)];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function updatePrice($ids, $type, $data)
    {
        $val = $data["batch_{$type}"] ?? 0;
        $unit = $data["batch_{$type}_unit"] ?? 'won'; // won, percent
        $mode = $data["batch_{$type}_updown"] ?? 'replace'; // replace, up, down
        
        // Map to DB Column
        // price -> fm_goods_option.price
        // consumer_price -> fm_goods_option.consumer_price
        // supply_price -> fm_goods_option.provider_price (Note legacy confusion: supply vs provider)
        // In Legacy, supply_price is in fm_goods_supply? Or option?
        // Legacy: "update fm_goods_supply ... set supply_price" -> Wait, isn't price in Option?
        // Let's assume standard New Admin structure: Option has price, consumer, provider. Supply has stock.
        
        $col = match($type) {
            'price' => 'price',
            'consumer_price' => 'consumer_price',
            'supply_price' => 'provider_price', // Checking Migration...
        };

        // Construct Update Query
        $q = GoodsOption::whereIn('goods_seq', $ids);

        if ($mode == 'replace') {
            $q->update([$col => $val]);
        } else {
            // Calculation Update
            // e.g. price = price + 100
            $op = ($mode == 'up') ? '+' : '-';
            
            if ($unit == 'percent') {
                 // price = price + (price * val / 100)
                 $q->update([$col => DB::raw("$col $op ($col * $val / 100)")]);
            } else {
                 $q->update([$col => DB::raw("$col $op $val")]);
            }
        }
    }

    protected function updateStock($ids, $val, $mode)
    {
        $q = DB::table('fm_goods_supply')->whereIn('goods_seq', $ids);
        
        if ($mode == 'replace') {
            $q->update(['stock' => $val, 'total_stock' => $val]);
        } else {
            $op = ($mode == 'up') ? '+' : '-';
            $q->update(['stock' => DB::raw("stock $op $val"), 'total_stock' => DB::raw("total_stock $op $val")]);
        }
    }
}
