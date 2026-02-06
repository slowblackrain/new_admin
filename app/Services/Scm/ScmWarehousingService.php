<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use App\Services\Scm\ScmLedgerService;
use Carbon\Carbon;
use Exception;

class ScmWarehousingService
{
    protected $ledgerService;

    public function __construct(ScmLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * 입고 목록 조회
     */
    public function getWarehousingList(array $filters)
    {
        $query = DB::table('fm_scm_warehousing as w')
            ->select('w.*', 't.trader_name', 'wh.wh_name')
            ->leftJoin('fm_scm_trader as t', 'w.trader_seq', '=', 't.trader_seq')
            ->leftJoin('fm_scm_warehouse as wh', 'w.wh_seq', '=', 'wh.wh_seq');

        // Date Filter
        if (!empty($filters['sc_sdate']) && !empty($filters['sc_edate'])) {
            $dateField = $filters['sc_date_fld'] ?? 'regist_date';
             // Legacy uses 00:00:00 to 23:59:59
            $query->whereBetween("w.{$dateField}", [$filters['sc_sdate'] . ' 00:00:00', $filters['sc_edate'] . ' 23:59:59']);
        }

        // Status Filter
        if (isset($filters['sc_whs_status']) && $filters['sc_whs_status'] !== '') {
            $query->where('w.whs_status', $filters['sc_whs_status']);
        }

        // Type Filter (Normal/Exception)
        if (!empty($filters['sc_whs_type'])) {
             if (is_array($filters['sc_whs_type'])) {
                 $query->whereIn('w.whs_type', $filters['sc_whs_type']);
             } else {
                 $query->where('w.whs_type', $filters['sc_whs_type']);
             }
        }

        // Keyword Filter
        if (!empty($filters['keyword'])) {
             $keyword = $filters['keyword'];
             $query->where(function($q) use ($keyword) {
                 $q->where('w.whs_code', 'like', "%{$keyword}%")
                   ->orWhere('t.trader_name', 'like', "%{$keyword}%");
             });
        }

        return $query->orderByDesc('w.whs_seq')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * 입고 상세 조회 (with Goods)
     */
    public function getWarehousingData($whsSeq)
    {
        $whs = DB::table('fm_scm_warehousing as w')
            ->select('w.*', 't.trader_name', 'o.sorder_code', 'o.sorder_type')
            ->leftJoin('fm_scm_trader as t', 'w.trader_seq', '=', 't.trader_seq')
            ->leftJoin('fm_scm_order as o', 'w.sorder_seq', '=', 'o.sorder_seq')
            ->where('w.whs_seq', $whsSeq)
            ->first();

        if (!$whs) return null;

        $items = DB::table('fm_scm_warehousing_goods as wg')
            ->select('wg.*', 'g.goods_name', 'g.goods_code', 'op.option_name', 'op.consumer_price')
            ->leftJoin('fm_goods as g', 'wg.goods_seq', '=', 'g.goods_seq')
            ->leftJoin('fm_goods_option as op', 'wg.option_seq', '=', 'op.option_seq')
            ->where('wg.whs_seq', $whsSeq)
            ->get();

        $whs->items = $items;
        return $whs;
    }

    /**
     * 입고 저장 (Process Warehousing)
     * Supports both Normal (Existing Order) and Exception (No Order) types.
     * 
     * @param array $data Input data
     */
    public function saveWarehousing(array $data)
    {
        $whsStatus = $data['status'] ?? '0'; // 0: Draft, 1: Complete
        $whsType = $data['whs_type'] ?? 'S'; // S: Standard, E: Exception
        $sorderSeq = $data['sorder_seq'] ?? null;
        $traderSeq = $data['trader_seq'] ?? null;
        $whSeq = $data['in_wh_seq'] ?? 1; // Default Warehouse
        $adminMemo = $data['admin_memo'] ?? '';
        
        $items = isset($data['goods_seq']) ? $this->parseItems($data) : [];

        if (empty($items)) throw new Exception("입고할 상품이 없습니다.");

        DB::beginTransaction();
        try {
            // 1. 비정규 입고(E)인 경우 -> 임의 발주서 생성 (Legacy: save_except_sorder)
            if ($whsType == 'E') {
                $sorderData = $this->createExceptionOrder($traderSeq, $items);
                $sorderSeq = $sorderData['sorder_seq'];
            }

            // 2. Validate Order Existence
            $order = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->first();
            if (!$order) throw new Exception("유효하지 않은 발주서입니다.");

            // 3. Create/Update Warehousing Header
            // Assuming Create for now. Implementing Update would require whs_seq check.
            $whsCode = 'WHS' . ($whsType == 'E' ? 'E' : 'S') . date('YmdHis') . rand(100,999);
            
            $whsId = DB::table('fm_scm_warehousing')->insertGetId([
                'whs_code' => $whsCode,
                'whs_type' => $whsType,
                'whs_status' => $whsStatus,
                'trader_seq' => $traderSeq,
                'sorder_seq' => $sorderSeq,
                'wh_seq' => $whSeq,
                'admin_memo' => $adminMemo,
                'regist_date' => Carbon::now(),
                'complete_date' => ($whsStatus == '1') ? Carbon::now() : null,
            ]);

            // 4. Save Warehousing Goods & Update Stock/Ledger (Only if Complete)
            foreach ($items as $item) {
                // Insert Record
                $whsGoodsId = DB::table('fm_scm_warehousing_goods')->insertGetId([
                    'whs_seq' => $whsId,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type'] ?? 'option',
                    'ea' => $item['ea'],
                    'supply_price' => $item['supply_price'],
                    'krw_supply_price' => $item['supply_price'], // Assuming KRW match for now
                    'location_code' => '1',
                    'location_position' => '1',
                ]);

                if ($whsStatus == '1') {
                    // A. Update Stock
                    DB::table('fm_goods_supply')
                        ->where('goods_seq', $item['goods_seq'])
                        ->where('option_seq', $item['option_seq'])
                        ->increment('stock', $item['ea']);
                    
                    // B. Update Sorder Goods (Received Qty)
                    DB::table('fm_scm_order_goods')
                        ->where('sorder_seq', $sorderSeq)
                        ->where('goods_seq', $item['goods_seq'])
                        ->where('option_seq', $item['option_seq'])
                        ->increment('whs_ea', $item['ea']);

                    // C. Ledger Entry
                    $this->ledgerService->updateDailyLedger($whSeq, [[
                        'goods_seq' => $item['goods_seq'], 
                        'option_seq' => $item['option_seq'],
                        'option_type' => $item['option_type'] ?? 'option'
                    ]]);
                }
            }

            // 5. Update Order Status if Fully Received
            if ($whsStatus == '1') {
                $incomplete = DB::table('fm_scm_order_goods')
                    ->where('sorder_seq', $sorderSeq)
                    ->whereColumn('ea', '>', 'whs_ea')
                    ->exists();
                
                if (!$incomplete) { // All received
                    DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->update(['sorder_status' => 2]);
                }
            }

            DB::commit();
            return $whsId;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Parse items from request array
     */
    private function parseItems($data)
    {
        $items = [];
        if (isset($data['goods_seq']) && is_array($data['goods_seq'])) {
            foreach ($data['goods_seq'] as $idx => $goodsSeq) {
                $ea = (int)str_replace(',', '', $data['ea'][$idx] ?? 0);
                if ($ea <= 0) continue;

                $items[] = [
                    'goods_seq' => $goodsSeq,
                    'option_seq' => $data['option_seq'][$idx],
                    'option_type' => $data['option_type'][$idx] ?? 'option',
                    'ea' => $ea,
                    'supply_price' => (float)str_replace(',', '', $data['supply_price'][$idx] ?? 0),
                    'supply_tax' => (float)str_replace(',', '', $data['supply_tax'][$idx] ?? 0),
                ];
            }
        }
        return $items;
    }

    /**
     * 비정규 입고 시 임의 발주서 생성 (Legacy: save_except_sorder)
     */
    private function createExceptionOrder($traderSeq, $items)
    {
        // 1. Calculate Totals
        $totalEa = 0;
        $totalSupply = 0;
        $totalTax = 0;
        foreach ($items as $item) {
            $totalEa += $item['ea'];
            $totalSupply += ($item['supply_price'] * $item['ea']);
            $totalTax += ($item['supply_tax'] * $item['ea']);
        }

        // 2. Create Header
        $sorderCode = 'EC' . date('YmdHis') . rand(100,999); // Exception Code
        $sorderSeq = DB::table('fm_scm_order')->insertGetId([
            'sorder_code' => $sorderCode,
            'sorder_type' => 'T', // Temporary/Exception
            'sorder_status' => '1', // Auto-complete for Exception
            'trader_seq' => $traderSeq,
            'total_ea' => $totalEa,
            'krw_total_supply_price' => $totalSupply,
            'krw_total_supply_tax' => $totalTax,
            'krw_total_price' => $totalSupply + $totalTax,
            'regist_date' => Carbon::now(),
            'complete_date' => Carbon::now(),
            'admin_memo' => '비정규 입고에 의한 자동 생성',
        ]);

        // 3. Create Details
        foreach ($items as $item) {
            // Need goods info for name/code... simplifying to IDs for now or fetch
            $goods = DB::table('fm_goods')->where('goods_seq', $item['goods_seq'])->first();
            $option = DB::table('fm_goods_option')->where('option_seq', $item['option_seq'])->first();

            DB::table('fm_scm_order_goods')->insert([
                'sorder_seq' => $sorderSeq,
                'goods_seq' => $item['goods_seq'],
                'option_seq' => $item['option_seq'],
                'option_type' => $item['option_type'],
                'goods_code' => $goods->goods_code ?? '',
                'goods_name' => $goods->goods_name ?? '',
                'option_name' => $option->option_name ?? '',
                'ea' => $item['ea'],
                'whs_ea' => 0, // Will be updated by warehousing logic
                'supply_price' => $item['supply_price'],
                'krw_supply_price' => $item['supply_price'],
                'supply_tax' => $item['supply_tax'],
                'add_reason' => '수동',
            ]);
        }

        return ['sorder_seq' => $sorderSeq, 'sorder_code' => $sorderCode];
    }
}
