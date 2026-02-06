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
     * Get Order List with Filtering
     */
    public function getOrderList(array $filters)
    {
        $query = DB::table('fm_scm_order as o')
            ->select('o.*', 't.trader_name')
            ->leftJoin('fm_scm_trader as t', 'o.trader_seq', '=', 't.trader_seq');

        // Date Filter
        if (!empty($filters['sc_sdate']) && !empty($filters['sc_edate'])) {
            $dateField = $filters['sc_date_fld'] ?? 'regist_date';
            $query->whereBetween("o.{$dateField}", [$filters['sc_sdate'] . ' 00:00:00', $filters['sc_edate'] . ' 23:59:59']);
        }

        // Status Filter
        if (isset($filters['sc_sorder_status']) && $filters['sc_sorder_status'] !== '') {
            $query->where('o.sorder_status', $filters['sc_sorder_status']);
        }

        // Keyword Filter
        if (!empty($filters['keyword'])) {
             $keyword = $filters['keyword'];
             $query->where(function($q) use ($keyword) {
                 $q->where('o.sorder_code', 'like', "%{$keyword}%")
                   ->orWhere('t.trader_name', 'like', "%{$keyword}%");
             });
        }
        
        return $query->orderByDesc('o.sorder_seq')->paginate($filters['per_page'] ?? 20);
    }
    /**
     * Get Order Data with Items
     */
    public function getOrderData($sorderSeq)
    {
        $order = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->first();
        if (!$order) return null;

        $items = DB::table('fm_scm_order_goods')
            ->where('sorder_seq', $sorderSeq)
            ->get();

        $order->items = $items;
        return $order;
    }

    /**
     * Get Auto Order Candidate List
     * Listing items where Stock < Safe Stock
     */
    public function getAutoOrderList($filters)
    {
        // Logic similar to ScmGoodsService but focused on reordering
        $query = DB::table('fm_goods as g')
            ->join('fm_goods_option as o', 'g.goods_seq', '=', 'o.goods_seq')
            ->join('fm_goods_supply as s', function($join) {
                $join->on('g.goods_seq', '=', 's.goods_seq')
                     ->on('o.option_seq', '=', 's.option_seq');
            })
            ->select(
                'g.goods_seq', 'g.goods_name', 'g.goods_code',
                'o.option_seq', 'o.option_name', 'o.option_code', 'o.consumer_price', 'o.price',
                's.supply_price', 's.stock', 's.safe_stock', 's.badstock', 's.total_stock'
            )
            // Filtering for Auto Order Candidates
            ->where('s.safe_stock', '>', 0) // Only items with safe stock set
            ->whereRaw('(s.stock) < s.safe_stock'); // Condition: Current < Safe

        // Keyword Filter
        if (!empty($filters['keyword'])) {
             $keyword = $filters['keyword'];
             $query->where(function($q) use ($keyword) {
                 $q->where('g.goods_name', 'like', "%{$keyword}%")
                   ->orWhere('g.goods_code', 'like', "%{$keyword}%");
             });
        }
        
        return $query->paginate($filters['per_page'] ?? 50);
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

    /**
     * SCM 발주 저장 (Legacy: save_sorder)
     */
    public function saveOrder(array $data, $sorderSeq = null)
    {
        DB::beginTransaction();
        try {
            // 1. 파라미터 정리 및 계산
            $preparedData = $this->chkSorderParam($data, $sorderSeq);
            $headerData = $preparedData['sorder'];
            $itemsData = $preparedData['goodsData'];

            // 2. 헤더 저장
            $sorderSeq = $this->saveSorderHeader($headerData, $sorderSeq);

            // 3. 상품 저장
            if (!empty($itemsData)) {
                $this->saveSorderGoods($sorderSeq, $itemsData);
            }

            // 4. 발주 완료 시 알림 (Legacy: sorder_draft_sender)
            if (isset($headerData['sorder_status']) && $headerData['sorder_status'] == 1) {
                // TODO: SMS/Email 발송 로직 (추후 구현)
                // $this->sendDraftNotification($sorderSeq);
            }

            DB::commit();
            return $sorderSeq;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 발주 파라미터 체크 및 데이터 가공 (Legacy: chk_sorder_param)
     */
    private function chkSorderParam(array $data, $sorderSeq)
    {
        $sorderStatus = $data['sorder_status'] ?? 0;
        $sorderType = $data['sorder_type'] ?? 'M';
        $traderSeq = $data['trader_seq'] ?? null;
        $adminMemo = $data['admin_memo'] ?? '';

        $totalEa = 0;
        $krwTotalSupplyPrice = 0;
        $krwTotalSupplyTax = 0;
        $krwTotalPrice = 0;
        
        $goodsData = [];
        $optionSeqs = $data['item_option_seq'] ?? []; 

        // 기존 정보 조회
        $oldOrder = null;
        if ($sorderSeq) {
            $oldOrder = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->first();
            if ($oldOrder && $oldOrder->sorder_status == 1) {
                $sorderStatus = 1;
            }
        }

        // 로그 생성
        $managerId = session('manager_id', 'admin'); 
        $logMsg = date('Y-m-d H:i:s') . " " . $managerId . "가 ";
        if ($oldOrder && $oldOrder->sorder_status) {
             $logMsg .= "발주서 관리메모를 수정하였습니다.";
        } elseif ($sorderStatus) {
             $logMsg .= "발주를 완료하였습니다.";
        } elseif ($sorderSeq) {
             $logMsg .= "발주를 수정하였습니다.";
        } else {
             $logMsg .= "발주를 등록대기하였습니다.";
        }
        $logMsg = '<div>' . $logMsg . ' (' . request()->ip() . ')</div>';

        if (!$traderSeq) {
            throw new \Exception("거래처를 선택해 주세요.");
        }

        // 상품 데이터 가공
        if (is_array($optionSeqs)) {
            foreach ($optionSeqs as $idx => $optSeq) {
                $ea = (int)str_replace(',', '', $data['item_ea'][$idx] ?? 0);
                if ($ea <= 0) continue;

                $supplyPrice = (float)str_replace(',', '', $data['item_supply_price'][$idx] ?? 0);
                $supplyTax = (float)str_replace(',', '', $data['item_supply_tax'][$idx] ?? 0);
                
                $krwSupplyPrice = $supplyPrice; 
                $krwSupplyTax = $supplyTax;

                $lineSupplyPrice = $supplyPrice * $ea;
                $lineTax = $supplyTax * $ea;

                $item = [
                    'goods_seq' => $data['item_goods_seq'][$idx],
                    'option_seq' => $optSeq,
                    'option_type' => $data['item_option_type'][$idx] ?? 'option', 
                    'goods_code' => $data['item_goods_code'][$idx] ?? '',
                    'goods_name' => $data['item_goods_name'][$idx] ?? '',
                    'option_name' => $data['item_option_name'][$idx] ?? '',
                    'supply_goods_name' => $data['item_supply_goods_name'][$idx] ?? '',
                    'ea' => $ea,
                    'supply_price' => $supplyPrice,
                    'supply_tax' => $supplyTax,
                    'krw_supply_price' => $krwSupplyPrice,
                    'krw_supply_tax' => $krwSupplyTax,
                    'add_reason' => $data['item_add_reason'][$idx] ?? '수동',
                ];

                $goodsData[] = $item;

                $totalEa += $ea;
                $krwTotalSupplyPrice += $lineSupplyPrice;
                $krwTotalSupplyTax += $lineTax;
            }
        }
        $krwTotalPrice = $krwTotalSupplyPrice + $krwTotalSupplyTax;

        if (empty($goodsData)) {
            throw new \Exception("발주 상품을 선택해 주세요.");
        }

        $sorder = [
            'sorder_status' => $sorderStatus,
            'sorder_type' => $sorderType,
            'trader_seq' => $traderSeq,
            'total_ea' => $totalEa,
            'admin_memo' => $adminMemo,
            'krw_total_supply_price' => $krwTotalSupplyPrice,
            'krw_total_supply_tax' => $krwTotalSupplyTax,
            'krw_total_price' => $krwTotalPrice,
            'chg_log' => $logMsg,
            'complete_date' => ($sorderStatus == 1) ? date('Y-m-d H:i:s') : null,
        ];

        return ['sorder' => $sorder, 'goodsData' => $goodsData];
    }

    /**
     * 발주 헤더 저장 (Legacy: save_sorder)
     */
    private function saveSorderHeader($data, $sorderSeq)
    {
        if ($sorderSeq) {
            if (isset($data['chg_log'])) {
                $log = $data['chg_log'];
                unset($data['chg_log']);
                DB::statement("UPDATE fm_scm_order SET chg_log = CONCAT(IFNULL(chg_log, ''), ?) WHERE sorder_seq = ?", [$log, $sorderSeq]);
            }
            DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->update($data);
        } else {
            $data['regist_date'] = date('Y-m-d H:i:s');
            $sorderSeq = DB::table('fm_scm_order')->insertGetId($data);

            $codePrefix = ($data['sorder_type'] == 'A') ? 'RC' : 'OC';
            $sorderCode = $codePrefix . date('YmdHis') . $sorderSeq;
            
            DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->update(['sorder_code' => $sorderCode]);
        }
        return $sorderSeq;
    }

    /**
     * 발주 상품 저장 (Legacy: save_sorder_goods)
     */
    private function saveSorderGoods($sorderSeq, $goodsData)
    {
        DB::table('fm_scm_order_goods')->where('sorder_seq', $sorderSeq)->delete();

        foreach ($goodsData as $item) {
            $item['sorder_seq'] = $sorderSeq;
            DB::table('fm_scm_order_goods')->insert($item);
        }
    }
}
