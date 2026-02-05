<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use Carbon\Carbon;
use Exception;
use App\Services\Scm\ScmLedgerService;

class ScmOrderService
{
    protected $ledgerService;

    public function __construct(ScmLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }
    /**
     * Confirm selected Auto Order Drafts and convert them to Real Orders (Sorders)
     * Groups by Trader and creates fm_scm_order + fm_scm_order_goods.
     * Deletes from fm_scm_autoorder_order.
     * 
     * @param array $aooSeqs
     * @return array Created SOrder Seqs
     */
    public function confirmAutoOrders(array $aooSeqs)
    {
        if (empty($aooSeqs)) return [];

        $drafts = DB::table('fm_scm_autoorder_order')
            ->whereIn('aoo_seq', $aooSeqs)
            ->get();

        if ($drafts->isEmpty()) return [];

        // Group by Trader
        $grouped = $drafts->groupBy('trader_seq');
        $createdOrderSeqs = [];

        DB::transaction(function() use ($grouped, &$createdOrderSeqs, $aooSeqs) {
            foreach ($grouped as $traderSeq => $items) {
                // Calculate Totals per Trader
                $totalEa = 0;
                $totalSupplyPrice = 0;
                $totalTax = 0;
                $totalPrice = 0;

                foreach ($items as $item) {
                     $ea = $item->ea;
                     $supply = $item->supply_price * $ea;
                     // Assuming tax is included or we calculate generic 10% if type requires, 
                     // but likely stored in item or 0 for now as per previous logic.
                     // Using 0 for tax as per initial simplified implementation.
                     $tax = 0; 
                     
                     $totalEa += $ea;
                     $totalSupplyPrice += $supply;
                     $totalTax += $tax;
                     $totalPrice += ($supply + $tax);
                }

                // 1. Create SOrder Header
                $sorderCode = 'OC' . date('YmdHis') . rand(100,999); 
                
                $orderId = DB::table('fm_scm_order')->insertGetId([
                    'sorder_code' => $sorderCode,
                    'trader_seq' => $traderSeq,
                    'sorder_status' => 1, // 1: Ordered
                    'sorder_type' => 'A', // A: Auto? Default M. Let's use A for Auto.
                    'total_ea' => $totalEa,
                    'krw_total_supply_price' => $totalSupplyPrice,
                    'krw_total_supply_tax' => $totalTax,
                    'krw_total_price' => $totalPrice,
                    'regist_date' => Carbon::now(),
                ]);

                $createdOrderSeqs[] = $orderId;

                // 2. Insert Items
                foreach ($items as $item) {
                     DB::table('fm_scm_order_goods')->insert([
                        'sorder_seq' => $orderId,
                        'goods_seq' => $item->goods_seq,
                        'option_seq' => $item->option_seq,
                        'option_type' => $item->option_type, 
                        'ea' => $item->ea,
                        'supply_price' => $item->supply_price,
                        'supply_tax' => 0, 
                        'supply_price_type' => $item->supply_price_type,
                        'goods_name' => $item->goods_name,
                        'option_name' => $item->option_name,
                        'goods_code' => $item->goods_code,
                     ]);
                }
            }

            // 3. Delete Drafts
            DB::table('fm_scm_autoorder_order')->whereIn('aoo_seq', $aooSeqs)->delete();
        });

        return $createdOrderSeqs;
    }

    /**
     * Process Warehousing (Receive Goods)
     * Handles Stock Update, Warehousing History, and Order Status Update.
     * Supports Partial Warehousing.
     * 
     * @param int $sorderSeq
     * @param array $items Array of ['goods_seq', 'option_seq', 'ea', 'option_type']
     * @return int Warehousing Header ID (whs_seq)
     */
    public function processWarehousing($sorderSeq, array $items)
    {
        if (empty($items)) throw new Exception("No items provided for warehousing.");

        // Fetch Order Info
        $order = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->first();
        if (!$order) throw new Exception("Order not found.");

        $whsSeq = 0;

        DB::transaction(function() use ($sorderSeq, $items, $order, &$whsSeq) {
            // 1. Create Warehousing Header
            // Generate Code: WHS + Type(Manual:M/Auto:A?) + Date + Random
            // Legacy uses 'E' for Exception/Manual, otherwise Standard. 
            // Assuming Standard for Order Processing.
            $whsCode = 'WHS' . 'S' . date('YmdHis') . rand(100,999);
            
            // Calculate Total Price for Header
            $totalPrice = 0; 
            // Fetch prices from items... simplifying by summing up supplied items for now 
            // or we could fetch from DB. Let's calculate on the fly for history.

            $totalPrice = 0; 
            // Fetch prices from items... simplifying by summing up supplied items for now 
            // or we could fetch from DB. Let's calculate on the fly for history.

            $whsSeq = DB::table('fm_scm_warehousing')->insertGetId([
                'whs_code' => $whsCode,
                'whs_type' => 'S', // Standard
                'whs_status' => '1', // Complete
                'trader_seq' => $order->trader_seq,
                'sorder_seq' => $sorderSeq,
                'wh_seq' => 1, // Default to 1
                'regist_date' => Carbon::now(),
                'complete_date' => Carbon::now(), // Immediate complete
            ]);

            $ledgerTargets = [];

            foreach ($items as $item) {
                // Validate Item belongs to Order
                $orderItem = DB::table('fm_scm_order_goods')
                    ->where('sorder_seq', $sorderSeq)
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->first();
                
                if (!$orderItem) continue; // Skip invalid

                $ea = intval($item['ea']);
                if ($ea <= 0) continue;

                // 2. Insert Warehousing Goods History
                // Need to fetch supply price from order item to record cost
                DB::table('fm_scm_warehousing_goods')->insert([
                    'whs_seq' => $whsSeq,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $orderItem->option_type,
                    'ea' => $ea,
                    'supply_price' => $orderItem->supply_price,
                    'krw_supply_price' => $orderItem->krw_supply_price, 
                    'location_code' => '1-1-1', 
                    'location_position' => '1-1-1',
                ]);

                // 3. Update Order Goods (Warehoused Qty)
                DB::table('fm_scm_order_goods')
                    ->where('sorder_seq', $sorderSeq)
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->increment('whs_ea', $ea);

                // 4. Update Stock (fm_goods_supply)
                // Assuming we use Total Stock or specific option stock. 
                // Defaulting to increasing 'stock' column.
                // NOTE: Legacy change_store_stock is encrypted. 
                // Standard logic: Update stock specific to option.
                 DB::table('fm_goods_supply')
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->increment('stock', $ea);

                 // Also update total_stock? 
                 // Legacy likely has triggers or manual update. 
                 // Let's safe update total_stock if it exists and looks redundant
                 DB::table('fm_goods_supply')
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->increment('total_stock', $ea);
                
                // Collect for Ledger Update
                $ledgerTargets[] = [
                    'goods_seq' => $item['goods_seq'], 
                    'option_seq' => $item['option_seq'],
                    'option_type' => $orderItem->option_type
                ];
            }

            // 5. Check Order Completion Status
            // If all items (ea == whs_ea), set status to 2
            $incompleteItems = DB::table('fm_scm_order_goods')
                ->where('sorder_seq', $sorderSeq)
                ->whereColumn('ea', '>', 'whs_ea')
                ->exists();

            if (!$incompleteItems) {
                DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->update(['sorder_status' => 2]);
            }

            // 6. Update Ledger
            if (!empty($ledgerTargets)) {
                $this->ledgerService->updateDailyLedger(1, $ledgerTargets); // wh_seq = 1
            }
        });

        return $whsSeq;
    }

    /**
     * Create Auto Order Draft (Replicates scmmodel->save_autosorder_goods)
     * Inserts into `fm_scm_autoorder_order` based on stock/safe_stock conditions.
     *
     * @param array $goodsInfo ['goods_seq', 'goods_name', 'goods_code']
     * @param array $orderOption ['order_seq', 'order_ea']
     * @param array $goodsOption ['option_seq', 'option_type', 'consumer_price', 'price', 'stock', 'badstock', 'safe_stock', 'reservation25', 'suboption_code', 'suboption', 'option1'...'option5', 'optioncode1'...'optioncode5']
     * @param bool $compulsion Force auto order regardless of stock
     * @return bool|int Insert ID or false
     */
    public function createAutoOrderDraft(array $goodsInfo, array $orderOption, array $goodsOption, bool $compulsion = false)
    {
        $goodsSeq = $goodsInfo['goods_seq'];
        $goodsName = $goodsInfo['goods_name'];
        $goodsCode = $goodsInfo['goods_code'];
        
        $orderSeq = $orderOption['order_seq'];
        $orderEa = $orderOption['order_ea'];
        
        $optionSeq = $goodsOption['option_seq'];
        $optionType = $goodsOption['option_type'];
        $consumerPrice = $goodsOption['consumer_price'];
        //$price = $goodsOption['price']; // Unused in logic but present in legacy
        $stock = $goodsOption['stock'];
        $badStock = $goodsOption['badstock'] ?? 0;
        $safeStock = $goodsOption['safe_stock'] ?? 0;
        $reservation25 = $goodsOption['reservation25'] ?? 0;

        // Construct Option Name & Code
        $optionName = '';
        if ($optionType == 'suboption') {
            $goodsCode .= $goodsOption['suboption_code'] ?? '';
            $optionName = $goodsOption['suboption'] ?? '';
        } else {
            for ($fo = 1; $fo <= 5; $fo++) {
                $fld = 'option' . $fo;
                $codeFld = 'optioncode' . $fo;
                if (!empty($goodsOption[$fld])) {
                    $optionName .= $goodsOption[$fld];
                    $goodsCode .= $goodsOption[$codeFld] ?? '';
                }
            }
        }

        // Check Auto Order Condition
        $autoOrderCondition = '0';
        try {
            $scmConfig = DB::table('fm_scm_config')->first(); // Assuming single row config
            $autoOrderCondition = $scmConfig->auto_order_condition ?? '0'; 
        } catch (\Exception $e) {
            // Config table might be missing, default to 0
        }

        if ($compulsion) {
            $autoOrderCondition = '3';
        }

        $autoConfFlag = false;
        if ($autoOrderCondition == '1') { // Stock - Bad - Order < Safe
            $autoConfFlag = ($stock - $badStock - $orderEa) < $safeStock;
        } elseif ($autoOrderCondition == '2') { // Available - Order < Safe
            $autoConfFlag = (($stock - $reservation25 - $badStock) - $orderEa) < $safeStock;
        } elseif ($autoOrderCondition == '3') { // Always
            $autoConfFlag = true;
        } else { // Default: Stock - Order < Safe (Logic from legacy 'else' block)
            $autoConfFlag = ($stock - $orderEa) < $safeStock;
        }

        if ($autoConfFlag && $optionSeq > 0) {
            // Get Default Trader Info
            $sc = [
                'goods_seq' => $goodsSeq,
                'main_trade_type' => 'Y',
                'exists_trader' => 'Y',
                'option_seq' => ($optionType == 'suboption') ? 0 : $optionSeq,
                'suboption_seq' => ($optionType == 'suboption') ? $optionSeq : 0,
            ];
            
            $defaultInfo = $this->getOrderDefaultInfo($sc);

            if ($defaultInfo && isset($defaultInfo[0]) && $defaultInfo[0]->default_seq > 0) {
                $info = $defaultInfo[0];
                $supplyPrice = $info->supply_price;
                
                // Auto Calculation Logic
                if ($info->auto_type == 'Y') {
                    $consumerPriceExTax = ($consumerPrice * 10) / 11;
                    $supplyPrice = $consumerPriceExTax * ($info->supply_price * 0.01);
                    // TODO: Implement exchange_krw and cut_exchange_price if needed. For now assuming KRW/Direct match
                    $supplyPrice = floor($supplyPrice); // Basic flooring, refine later
                }
            } else {
                // Fallback if no default info
                $info = (object)[
                    'trader_seq' => '0',
                    'supply_goods_name' => '',
                    'supply_price_type' => 'KRW',
                    'use_supply_tax' => 'Y',
                ];
                $supplyPrice = '0';
            }

            // Insert into fm_scm_autoorder_order
            $insertParam = [
                'order_seq' => $orderSeq,
                'goods_seq' => $goodsSeq,
                'goods_name' => $goodsName,
                'goods_code' => $goodsCode,
                'option_type' => $optionType,
                'option_seq' => $optionSeq,
                'option_name' => $optionName,
                'ea' => $orderEa,
                'safe_stock' => $safeStock,
                'trader_seq' => $info->trader_seq,
                'supply_goods_name' => $info->supply_goods_name,
                'use_tax' => $info->use_supply_tax,
                'supply_price_type' => $info->supply_price_type,
                'supply_price' => $supplyPrice,
                'regist_date' => Carbon::now(),
            ];

            return DB::table('fm_scm_autoorder_order')->insertGetId($insertParam);
        }

        return false;
    }

    /**
     * Replicates scmmodel->get_order_defaultinfo logic
     */
    private function getOrderDefaultInfo(array $sc)
    {
        $query = DB::table('fm_scm_order_defaultinfo as fsod')
            ->select('fsod.*', 'fst.trader_name', 'fst.trader_seq')
            ->leftJoin('fm_scm_trader as fst', 'fsod.trader_seq', '=', 'fst.trader_seq')
            ->where('fsod.default_seq', '>', 0);

        if (!empty($sc['exists_trader']) && $sc['exists_trader'] == 'Y') {
            // Inner Join if trader exists required
            $query = DB::table('fm_scm_order_defaultinfo as fsod')
                ->select('fsod.*', 'fst.trader_name', 'fst.trader_seq')
                ->join('fm_scm_trader as fst', 'fsod.trader_seq', '=', 'fst.trader_seq')
                ->where('fsod.default_seq', '>', 0);
        }

        if (!empty($sc['main_trade_type'])) {
            $query->where('fsod.main_trade_type', $sc['main_trade_type']);
        }

        if (!empty($sc['goods_seq'])) {
            $query->where('fsod.goods_seq', $sc['goods_seq']);
        }

        if (!empty($sc['option_seq']) && $sc['option_seq'] > 0) {
            $query->where('fsod.option_type', 'option')
                  ->where('fsod.option_seq', $sc['option_seq']);
        }

        if (!empty($sc['suboption_seq']) && $sc['suboption_seq'] > 0) {
             $query->where('fsod.option_type', 'suboption')
                  ->where('fsod.option_seq', $sc['suboption_seq']);
        }

        $query->orderBy('fsod.goods_seq')
              ->orderBy('fsod.option_type')
              ->orderBy('fsod.option_seq')
              ->orderByDesc('fsod.default_seq');

        return $query->get()->toArray();
    }
}
