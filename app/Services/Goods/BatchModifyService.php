<?php

namespace App\Services\Goods;

use App\Models\Goods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchModifyService
{
    /**
     * Update Goods based on mode
     */
    public function update($mode, $ids, $data)
    {
        $results = ['success' => 0, 'fail' => 0, 'errors' => []];

        if (empty($ids) || !is_array($ids)) {
            $results['errors'][] = "No items selected.";
            return $results;
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                // Determine update method based on mode
                // Legacy allows distinct updates per row or bulk updates.
                // For simplicity in Phase 1, we assume form data sends array: goods_seq[], price[], stock[], etc.
                
                // Fetch current goods
                $goods = Goods::find($id);
                if (!$goods) {
                    $results['fail']++;
                    continue;
                }

                // Prepare update data
                $updateData = [];

                if ($mode == 'price') {
                    // Update fm_goods (Status, View)
                    $goodsData = array_intersect_key($this->preparePriceData($id, $data), array_flip([
                        'goods_view', 'goods_status'
                    ]));
                    if (!empty($goodsData)) {
                        $goods->update($goodsData);
                    }

                    // Update fm_goods_option (Price, Consumer Price)
                    $optionData = array_intersect_key($this->preparePriceData($id, $data), array_flip([
                        'price', 'consumer_price'
                    ]));
                    if (!empty($optionData)) {
                        $goods->defaultOption()->update($optionData);
                    }

                    // Update fm_goods_supply (Stock)
                    $stockData = array_intersect_key($this->preparePriceData($id, $data), array_flip([
                        'stock'
                    ]));
                    // Map default_stock to stock if present
                    if (isset($data['default_stock'][$id])) {
                        $stockData['stock'] = $data['default_stock'][$id];
                    }

                    if (!empty($stockData)) {
                        DB::table('fm_goods_supply')->where('goods_seq', $id)->update($stockData);
                    }
                }
                
                $results['success']++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Batch Update Error: " . $e->getMessage());
            $results['fail'] = count($ids); // All failed due to rollback? Or singular?
            // Legacy usually does individual updates. Let's do transaction whole?
            // If one fails, user wants others to succeed generally.
            // But let's stick to transaction for integrity if possible, or per-item transaction.
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    protected function preparePriceData($id, $data)
    {
        // $data contains arrays keyed by field name, e.g. $data['price'][$id]
        $fields = [
            'price', 'consumer_price', 'b2b_price_rate', 'default_stock', 'runout_policy', 
            'able_stock_limit', 'goods_view', 'goods_status'
        ]; // Add more as needed

        $updateData = [];
        foreach ($fields as $field) {
            if (isset($data[$field][$id])) {
                $value = $data[$field][$id];
                // Sanitize/Validate if needed
                $updateData[$field] = $value;
                
                // Special handling: Stock logic (fm_goods table has tot_stock, but batch modify often targets default_stock or supply stock)
                // Legacy: user modifies 'default_stock', system calculates real stock.
                // Parity: Update the matched column.
            }
        }
        return $updateData;
    }
}
