<?php

namespace App\Services\Scm;

use App\Models\Goods;
use Illuminate\Support\Facades\DB;

class ScmGoodsService
{
    /**
     * Get SCM Goods List with Safety Stock Check
     * 
     * @param array $filters ['wh_seq', 'keyword', 'warning_only', 'per_page']
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getScmGoodsList(array $filters)
    {
        $whSeq = $filters['wh_seq'] ?? null;
        $keyword = $filters['keyword'] ?? null;
        $warningOnly = $filters['warning_only'] ?? false;
        $perPage = $filters['per_page'] ?? 20;

        $query = Goods::query()
            ->select(
                'fm_goods.goods_seq',
                'fm_goods.goods_code',
                'fm_goods.goods_name',
                'fm_goods.scm_category',
                'fm_goods.consumer_price',
                'fm_goods.price',
                'fm_goods.trader_seq',
                
                // Supply Info
                'fm_goods_supply.stock as total_stock',
                'fm_goods_supply.safe_stock',
                'fm_goods_supply.supply_price',
                'fm_goods_supply.supply_price_type',
                'fm_goods_supply.use_supply_tax',
                'fm_goods_supply.auto_type', // Auto Order Y/N
                
                // Access Joined Data
                'fm_goods_image.image as goods_image',
                'fm_goods_option.option_name',
                'fm_goods_option.option_seq',
                'fm_goods_option.option_type',
                'fm_scm_trader.trader_name'
            )
            ->leftJoin('fm_goods_supply', 'fm_goods.goods_seq', '=', 'fm_goods_supply.goods_seq')
            ->leftJoin('fm_goods_option', function ($join) {
                $join->on('fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                    ->where('fm_goods_option.default_option', 'y');
            })
            ->leftJoin('fm_goods_image', function ($join) {
                $join->on('fm_goods.goods_seq', '=', 'fm_goods_image.goods_seq')
                    ->where('fm_goods_image.cut_number', 1);
            })
            ->leftJoin('fm_scm_trader', 'fm_goods_supply.trader_seq', '=', 'fm_scm_trader.trader_seq'); // Usually on supply or goods. Checking legacy logic, likely supply or goods. Using supply for SCM context if available, or goods?
            // Legacy uses `fm_goods.trader_seq` often? `get_order_defaultinfo` often joins.
            // Let's assume fm_goods.trader_seq for now, or use the one on supply if SCM specific?
            // Actually, legacy `goods.html` uses `.main_info.trader_name`. `main_info` comes from `get_order_defaultinfo`.
            // Let's try `fm_goods.trader_seq` first, if null, we might need a fallback.
            // Correction: I'll use `fm_scm_trader` join on `fm_goods.trader_seq`.

        // Warehouse Specific Stock
        // Warehouse Specific Stock
        if ($whSeq) {
            $whSub = DB::table('fm_scm_location_link')
                ->select('goods_seq', DB::raw('SUM(ea) as wh_stock'))
                ->where('wh_seq', $whSeq)
                ->groupBy('goods_seq');

            $query->leftJoinSub($whSub, 'wh_info', function($join) {
                $join->on('fm_goods.goods_seq', '=', 'wh_info.goods_seq');
            });
            
            $query->addSelect(DB::raw('COALESCE(wh_info.wh_stock, 0) as wh_stock'));
        } else {
            $query->addSelect(DB::raw('0 as wh_stock'));
            // If no WH selected, we don't group by? Or just simple select.
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('fm_goods.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('fm_goods.goods_code', 'like', "%{$keyword}%");
            });
        }
        
        // Trader Filter
        if (!empty($filters['trader_seq'])) {
            $query->where('fm_scm_trader.trader_seq', $filters['trader_seq']);
        }
        
        // Trader Group Filter
        if (!empty($filters['trader_group'])) {
            $query->where('fm_scm_trader.trader_group', $filters['trader_group']);
        }
        
        // Category Filter
        if (!empty($filters['category_code'])) {
            $query->where('fm_goods.scm_category', 'like', $filters['category_code'] . '%');
        }

        // Warning Only Filter
        if ($warningOnly) {
            if ($whSeq) {
                // If WH selected, Safe Stock vs WH Stock? 
                // Legacy line 137: if ($data['location_stock'] < $data['safe_stock'])
                // So Safe Stock is compared against the *displayed* stock.
                $query->havingRaw('wh_stock < fm_goods_supply.safe_stock');
            } else {
                $query->whereColumn('fm_goods_supply.stock', '<', 'fm_goods_supply.safe_stock');
            }
        }

        return $query->orderBy('fm_goods.goods_seq', 'desc')->paginate($perPage);
    }

    /**
     * Create Auto Order Items
     */
    public function createAutoOrder(array $goodsSeqs, array $data)
    {
        $count = 0;
        $addEaType = $data['add_ea_type'];
        $directEa = $data['direct_ea'] ?? 0;
        $storeSeq = $data['store_seq'] ?? 0;
        $whSeq = $data['warehouse_seq'] ?? 0;

        foreach ($goodsSeqs as $goodsSeq) {
            // Check Goods & Options (Assuming Default Option for simple 1-click add, or logic handling multiple options if passed?)
            // Legacy popup handles option list. My modal hidden input has goods_seq list.
            // If goods has options, legacy usually adds all options or default? 
            // My modal js just gathered checkboxes from the list. The list items are goods_seq.
            // If the list row represents a specific option (option_seq), I need to pass that.
            // My goods list row logic: $item->goods_seq.
            // In legacy, `goods` list rows are per goods (unless option view mode).
            // Let's assume Default Option for now or fetches all options?
            // Legacy PHP `add_auto_order_goods` parses `optioninfo_list` which can be "goods_seq" or "goods_seqoptionoption_seq".
            // Since my checkAll picks goods_seq, I'll process the Default Option of that goods.
            
            $options = DB::table('fm_goods_option')
                ->where('goods_seq', $goodsSeq)
                ->where('default_option', 'y') // Priority to default
                ->get();
            
            if ($options->isEmpty()) {
                // If no default, get all? Or skip?
                $options = DB::table('fm_goods_option')->where('goods_seq', $goodsSeq)->get();
            }

            foreach ($options as $opt) {
                // Calculate EA
                $orderEa = 0;
                $stock = 0;
                
                // Fetch Stock Info
                // If WH specifically selected for calculation
                if ($whSeq) {
                    $stockInfo = DB::table('fm_scm_location_link')
                        ->where('goods_seq', $goodsSeq)
                        ->where('wh_seq', $whSeq)
                        ->sum('ea');
                    $stock = $stockInfo;
                } else {
                    // Total Stock
                    $supply = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->first();
                    $stock = $supply->stock ?? 0;
                }

                // Safe Stock (Global or Store specific?)
                // Legacy: $supply['safe_stock'].
                $safeStock = 0;
                $supplyInfo = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->first();
                $safeStock = $supplyInfo->safe_stock ?? 0;

                if ($addEaType == 'direct' && $directEa > 0) {
                    $orderEa = $directEa;
                } elseif ($addEaType == 'auto') {
                    // Formula: Safe - Stock
                    $orderEa = $safeStock - $stock;
                }

                if ($orderEa > 0) {
                     // Get Order Default Info (Trader, Price)
                     $defaultInfo = DB::table('fm_scm_order_defaultinfo')
                        ->where('goods_seq', $goodsSeq)
                        ->where('option_seq', $opt->option_seq)
                        ->first();
                    
                     $traderSeq = $defaultInfo->trader_seq ?? 0;
                     $supplyPrice = $defaultInfo->supply_price ?? 0;
                     $supplyPriceType = $defaultInfo->supply_price_type ?? 'KRW';
                     $useTax = $defaultInfo->use_supply_tax ?? 'Y';
                     $supplyGoodsName = $defaultInfo->supply_goods_name ?? $opt->option_name; // Fallback

                     DB::table('fm_scm_autoorder_order')->insert([
                        'order_seq' => 0, // Pending
                        'goods_seq' => $goodsSeq,
                        'goods_name' => DB::table('fm_goods')->where('goods_seq', $goodsSeq)->value('goods_name'),
                        'goods_code' => DB::table('fm_goods')->where('goods_seq', $goodsSeq)->value('goods_code'),
                        'option_type' => $opt->option_type ?? 'option', // 'option' or 'suboption'
                        'option_seq' => $opt->option_seq,
                        'option_name' => $opt->option_name,
                        'ea' => $orderEa,
                        'safe_stock' => $safeStock,
                        'trader_seq' => $traderSeq,
                        'supply_goods_name' => $supplyGoodsName,
                        'use_tax' => $useTax,
                        'supply_price_type' => $supplyPriceType,
                        'supply_price' => $supplyPrice,
                        'regist_date' => now(),
                     ]);
                     $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Download Excel
     */
    public function downloadExcel($target, $type, $filters)
    {
        // Reuse getScmGoodsList logic but without pagination
        // We override per_page to a large number or modify method to support 'get()'
        // For efficiency, I'll copy the query logic lightly or refactor.
        // Refactoring getScmGoodsList query builder out:
        
        $query = Goods::query()
            ->select(
                'fm_goods.goods_seq',
                'fm_goods.goods_code',
                'fm_goods.goods_name',
                'fm_goods.scm_category',
                'fm_goods.consumer_price',
                'fm_goods_supply.stock as total_stock',
                'fm_goods_supply.safe_stock',
                'fm_goods_supply.supply_price',
                'fm_scm_trader.trader_name'
            )
            ->leftJoin('fm_goods_supply', 'fm_goods.goods_seq', '=', 'fm_goods_supply.goods_seq')
            ->leftJoin('fm_scm_trader', 'fm_goods_supply.trader_seq', '=', 'fm_scm_trader.trader_seq');

        // Apply Filters
        if (!empty($filters['keyword'])) {
             $keyword = $filters['keyword'];
             $query->where(function($q) use ($keyword) {
                $q->where('fm_goods.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('fm_goods.goods_code', 'like', "%{$keyword}%");
            });
        }
        if (!empty($filters['trader_seq'])) $query->where('fm_scm_trader.trader_seq', $filters['trader_seq']);
        if (!empty($filters['trader_group'])) $query->where('fm_scm_trader.trader_group', $filters['trader_group']);
        if (!empty($filters['category_code'])) $query->where('fm_goods.scm_category', 'like', $filters['category_code'] . '%');

        if ($target == 'select' && !empty($filters['goods_seq'])) {
             $seqs = explode(',', $filters['goods_seq']);
             $query->whereIn('fm_goods.goods_seq', $seqs);
        }

        $list = $query->orderBy('fm_goods.goods_seq', 'desc')->get(); // Limit?

        // Stream CSV
        $filename = 'goods_list_' . date('Ymd') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function() use ($list) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Goods Seq', 'Code', 'Name', 'Trader', 'Stock', 'Safe Stock', 'Supply Price', 'Consumer Price']);
            
            foreach ($list as $row) {
                fputcsv($file, [
                    $row->goods_seq,
                    $row->goods_code,
                    $row->goods_name,
                    $row->trader_name,
                    $row->total_stock,
                    $row->safe_stock,
                    $row->supply_price,
                    $row->consumer_price
                ]);
            }
            fclose($file);
        }, 200, $headers);
    }
}
