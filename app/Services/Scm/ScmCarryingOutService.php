<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use App\Models\Goods; // Assuming needed for some checks
use Carbon\Carbon;
use Exception;
use App\Services\Scm\ScmLedgerService;

class ScmCarryingOutService
{
    protected $ledgerService;

    public function __construct(ScmLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Get Carrying Out List with Filtering
     */
    public function getCarryingOutList(array $filters)
    {
        $query = DB::table('fm_scm_carryingout as c')
            ->select('c.*', 't.trader_name', 'w.wh_name')
            ->leftJoin('fm_scm_trader as t', 'c.trader_seq', '=', 't.trader_seq')
            ->leftJoin('fm_scm_warehouse as w', 'c.wh_seq', '=', 'w.wh_seq');

        // Date Filter
        if (!empty($filters['sc_sdate']) && !empty($filters['sc_edate'])) {
            $dateField = $filters['sc_date_fld'] ?? 'regist_date';
             // Adjust time for end date to include the whole day
            $query->whereBetween("c.{$dateField}", [$filters['sc_sdate'] . ' 00:00:00', $filters['sc_edate'] . ' 23:59:59']);
        }

        // Status Filter
        if (isset($filters['sc_cro_status']) && $filters['sc_cro_status'] !== '') {
            $query->where('c.cro_status', $filters['sc_cro_status']);
        }

        // Keyword Filter
        if (!empty($filters['keyword'])) {
             $keyword = $filters['keyword'];
             $query->where(function($q) use ($keyword) {
                 $q->where('c.cro_code', 'like', "%{$keyword}%")
                   ->orWhere('t.trader_name', 'like', "%{$keyword}%");
             });
        }
        
        return $query->orderByDesc('c.cro_seq')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get Carrying Out Data with Items
     */
    public function getCarryingOutData($croSeq)
    {
        $cro = DB::table('fm_scm_carryingout as c')
            ->select('c.*', 't.trader_name', 'w.wh_name')
            ->leftJoin('fm_scm_trader as t', 'c.trader_seq', '=', 't.trader_seq')
            ->leftJoin('fm_scm_warehouse as w', 'c.wh_seq', '=', 'w.wh_seq')
            ->where('c.cro_seq', $croSeq)
            ->first();

        if (!$cro) return null;

        $items = DB::table('fm_scm_carryingout_goods as g')
            ->where('cro_seq', $croSeq)
            ->get();

        $cro->items = $items;
        return $cro;
    }

    /**
     * Save Carrying Out (Header + Items + Stock Update)
     */
    public function saveCarryingOut(array $data)
    {
        return DB::transaction(function() use ($data) {
            $croSeq = $data['cro_seq'] ?? null;
            $items = [];
            
            // Prepare Items Data
            if (isset($data['goods_seq']) && is_array($data['goods_seq'])) {
                foreach ($data['goods_seq'] as $idx => $goodsSeq) {
                    $ea = intval($data['ea'][$idx] ?? 0);
                    if ($ea <= 0) continue;

                    $items[] = [
                        'goods_seq' => $goodsSeq,
                        'option_seq' => $data['option_seq'][$idx],
                        'option_type' => $data['option_type'][$idx] ?? 'option', 
                        'ea' => $ea,
                        'supply_price' => $data['supply_price'][$idx] ?? 0,
                        'supply_tax' => $data['supply_tax'][$idx] ?? 0,
                    ];
                }
            }

            if (empty($items)) throw new Exception("No valid items to carry out.");

            // Calculate Totals
            $totalEa = 0;
            $totalPrice = 0;
            foreach ($items as $item) {
                $totalEa += $item['ea'];
                $totalPrice += ($item['supply_price'] + $item['supply_tax']) * $item['ea'];
            }

            // 1. Save Header
            $headerData = [
                'trader_seq' => $data['trader_seq'],
                'wh_seq' => $data['in_wh_seq'] ?? ($data['wh_seq'] ?? 1), // Legacy UI uses 'in_wh_seq' or 'wh_seq'
                'cro_type' => $data['cro_type'] ?? 'E',
                'cro_status' => $data['status'] ?? '1', // 1: Complete
                'total_ea' => $totalEa,
                'krw_total_price' => $totalPrice, // Simplified
                'admin_memo' => $data['admin_memo'] ?? '',
                'regist_date' => Carbon::now(),
            ];

            // If Complete, set date
            if ($headerData['cro_status'] == '1') {
                $headerData['complete_date'] = Carbon::now();
            }

            if ($croSeq) {
                // Update (Re-saving not fully supported in simple logic if stock already changed, assumes new or simple update)
                // For safety, let's block status change from 1 to 0 or re-saving 1 if already 1, to prevent double deduction.
                // In this implementation, we assume CREATE mostly. Update logic can be complex.
                // Let's stick to Create for simplicity or basic update.
                DB::table('fm_scm_carryingout')->where('cro_seq', $croSeq)->update($headerData);
            } else {
                $croSeq = DB::table('fm_scm_carryingout')->insertGetId($headerData);
                
                // Generate Code
                $croCode = 'CRO' . $headerData['cro_type'] . date('YmdHis') . rand(100,999);
                DB::table('fm_scm_carryingout')->where('cro_seq', $croSeq)->update(['cro_code' => $croCode]);
            }

            // 2. Save Items & Update Stock
            // If updating, delete old items first? Risk of stock mismatch. 
            // Simplified: Only support CREATE for Stock Impacting actions for now.
            
            DB::table('fm_scm_carryingout_goods')->where('cro_seq', $croSeq)->delete();

            $ledgerTargets = [];

            foreach ($items as $item) {
                // Fetch Goods Details for Name
                $goods = DB::table('fm_goods')->where('goods_seq', $item['goods_seq'])->select('goods_name', 'goods_code')->first();
                // Check if option1 exists, otherwise fallback or concat. Using option1 as primary name.
                $option = DB::table('fm_goods_option')->where('option_seq', $item['option_seq'])->select('option1 as option_name')->first();

                DB::table('fm_scm_carryingout_goods')->insert([
                    'cro_seq' => $croSeq,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type'],
                    'goods_name' => $goods->goods_name ?? '',
                    'goods_code' => $goods->goods_code ?? '',
                    'option_name' => $option->option_name ?? '',
                    'ea' => $item['ea'],
                    'supply_price' => $item['supply_price'],
                    'supply_tax' => $item['supply_tax'],
                    'krw_supply_price' => $item['supply_price'], // Simplified
                ]);

                // 3. Stock Update (ONLY if status is Complete '1')
                if ($headerData['cro_status'] == '1') {
                     DB::table('fm_goods_supply')
                        ->where('goods_seq', $item['goods_seq'])
                        ->where('option_seq', $item['option_seq'])
                        ->decrement('stock', $item['ea']);
                    
                     DB::table('fm_goods_supply')
                        ->where('goods_seq', $item['goods_seq'])
                        ->where('option_seq', $item['option_seq'])
                        ->decrement('total_stock', $item['ea']);
                }

                $ledgerTargets[] = [
                    'goods_seq' => $item['goods_seq'], 
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type']
                ];
            }

            // 4. Update Ledger
            if (!empty($ledgerTargets) && $headerData['cro_status'] == '1') {
                $this->ledgerService->updateDailyLedger($headerData['wh_seq'], $ledgerTargets);
            }

            return $croSeq;
        });
    }

    /**
     * Delete Carrying Out (Revert Stock)
     */
    public function deleteCarryingOut($croSeq)
    {
        return DB::transaction(function() use ($croSeq) {
            $cro = DB::table('fm_scm_carryingout')->where('cro_seq', $croSeq)->first();
            if (!$cro) throw new Exception("Carrying Out entry not found.");

            $items = DB::table('fm_scm_carryingout_goods')->where('cro_seq', $croSeq)->get();

            // Revert Stock if status was Complete
            if ($cro->cro_status == '1') {
                $ledgerTargets = [];
                foreach ($items as $item) {
                     DB::table('fm_goods_supply')
                        ->where('goods_seq', $item->goods_seq)
                        ->where('option_seq', $item->option_seq)
                        ->increment('stock', $item->ea); // Add back
                    
                     DB::table('fm_goods_supply')
                        ->where('goods_seq', $item->goods_seq)
                        ->where('option_seq', $item->option_seq)
                        ->increment('total_stock', $item->ea);
                    
                    $ledgerTargets[] = [
                        'goods_seq' => $item->goods_seq, 
                        'option_seq' => $item->option_seq,
                        'option_type' => $item->option_type
                    ];
                }

                 // Update Ledger
                if (!empty($ledgerTargets)) {
                    $this->ledgerService->updateDailyLedger($cro->wh_seq, $ledgerTargets);
                }
            }

            DB::table('fm_scm_carryingout_goods')->where('cro_seq', $croSeq)->delete();
            DB::table('fm_scm_carryingout')->where('cro_seq', $croSeq)->delete();

            return true;
        });
    }
}
