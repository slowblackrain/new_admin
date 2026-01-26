<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScmManageController extends Controller
{
    // List Goods for Revision
    public function revision(Request $request)
    {
        $query = DB::table('fm_goods as g')
            ->leftJoin('fm_goods_supply as sup', 'g.goods_seq', '=', 'sup.goods_seq')
            ->select(
                'g.goods_seq',
                'g.goods_name',
                'g.goods_code',
                DB::raw('IFNULL(sup.stock, 0) as current_stock')
            );

        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('g.goods_name', 'like', "%{$request->keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$request->keyword}%");
            });
        }

        $goods = $query->paginate(50);

        return view('admin.scm.manage.revision', compact('goods'));
    }

    // Save Stock Revision
    public function save_revision(Request $request)
    {
        $revisions = $request->input('stock'); // [goods_seq => new_stock]
        
        if (!$revisions || !is_array($revisions)) {
            return back()->with('error', '변경할 내역이 없습니다.');
        }

        DB::beginTransaction();
        try {
            // Create Revision Master Record
            $revision_code = 'R' . date('YmdHis');
            $revision_seq = DB::table('fm_scm_stock_revision')->insertGetId([
                'revision_code' => $revision_code,
                'revision_type' => 'def', // Basic/Adjustment
                'revision_status' => 1, // Complete
                'wh_seq' => 1, // Default warehouse
                'admin_memo' => 'Manual Revision via Admin',
                'total_ea' => 0, // Will update later
                'complete_date' => now(),
                'krw_total_supply_price' => 0,
                'krw_total_supply_tax' => 0,
                'krw_total_price' => 0,
                'regist_date' => now()
            ]);

            $total_diff = 0;
            $updated_count = 0;

            foreach ($revisions as $goods_seq => $new_stock) {
                // Ensure atomic read
                $current = DB::table('fm_goods_supply')->where('goods_seq', $goods_seq)->lockForUpdate()->first();
                $current_stock = $current ? $current->stock : 0;
                $new_stock = (int)$new_stock;

                if ($current_stock == $new_stock) continue;

                $diff = $new_stock - $current_stock;

                // Update Supply
                DB::table('fm_goods_supply')->updateOrInsert(
                    ['goods_seq' => $goods_seq],
                    ['stock' => $new_stock, 'total_stock' => $new_stock] // Simplified: sync total_stock
                );

                // Update Location Stock (Default Warehouse 1)
                $diff = $new_stock - $current_stock;
                $this->updateLocationStock(1, $goods_seq, $diff);

                // Insert Revision Detail
                DB::table('fm_scm_stock_revision_goods')->insert([
                    'revision_seq' => $revision_seq,
                    'goods_seq' => $goods_seq,
                    'goods_name' => '', // Lookup efficient? Maybe skip or fetch
                    'goods_code' => '',
                    'use_tax' => 'Y',
                    'option_type' => 'option',
                    'option_seq' => 0, // Assuming simple goods for now
                    'option_name' => '',
                    'ea' => $diff,
                    'supply_price_type' => 'KRW',
                    'supply_price' => 0,
                    'krw_supply_price' => 0,
                    'exchange_price' => 1,
                    'supply_tax' => 0,
                    'krw_supply_tax' => 0
                ]);

                $total_diff += abs($diff);
                $updated_count++;
            }

            if ($updated_count == 0) {
                DB::rollBack();
                return back()->with('warning', '변경된 재고가 없습니다.');
            }

            // Update Master Count
            DB::table('fm_scm_stock_revision')->where('revision_seq', $revision_seq)->update(['total_ea' => $total_diff]);

            DB::commit();
            return back()->with('success', "$updated_count 건의 재고가 조정되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '재고 조정 중 오류: ' . $e->getMessage());
        }
    }

    // Stock Ledger
    public function ledger(Request $request) {
        // 1. Warehousing History ( 입고 )
        $warehousing = DB::table('fm_offer as o')
            ->join('fm_goods as g', 'o.goods_seq', '=', 'g.goods_seq')
            ->where('o.step', 11) // Stocked
            ->select(
                DB::raw("'입고' as type"),
                'o.stock_date as date',
                'g.goods_name',
                'g.goods_code',
                'o.ord_stock as qty',
                'o.offer_code as note' 
            );

        // 2. Revision History ( 조정 )
        $revision = DB::table('fm_scm_stock_revision_goods as rg')
            ->join('fm_scm_stock_revision as r', 'rg.revision_seq', '=', 'r.revision_seq')
            ->where('r.revision_status', 1)
            ->select(
                DB::raw("'조정' as type"),
                'r.complete_date as date',
                'rg.goods_name',
                'rg.goods_code',
                'rg.ea as qty',
                'r.admin_memo as note'
            );
        
        // Search
        if ($request->keyword) {
            $warehousing->where(function($q) use ($request) {
                $q->where('g.goods_name', 'like', "%{$request->keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$request->keyword}%");
            });
            $revision->where(function($q) use ($request) {
                $q->where('rg.goods_name', 'like', "%{$request->keyword}%")
                  ->orWhere('rg.goods_code', 'like', "%{$request->keyword}%");
            });
        }

        // Union & Pagination
        $query = $warehousing->unionAll($revision);
        
        $logs = DB::query()->fromSub($query, 'ledger')
            ->orderBy('date', 'desc')
            ->simplePaginate(50);

        return view('admin.scm.manage.ledger', compact('logs'));
    }

    // Stock Movement List
    public function stockmove(Request $request)
    {
        $query = \App\Models\Scm\ScmStockMove::orderBy('regist_date', 'desc');

        // Basic Search
        if ($request->keyword) {
            $query->where('move_code', 'like', "%{$request->keyword}%");
        }

        $moves = $query->paginate(20);
        $warehouses = \App\Models\Scm\ScmWarehouse::all()->keyBy('wh_seq');

        return view('admin.scm.manage.stockmove', compact('moves', 'warehouses'));
    }

    // Stock Movement Form
    public function stockmove_regist(Request $request)
    {
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        
        // Populate if editing (not implemented in legacy often, but good to have logic placeholder)
        $move = null;
        if ($request->seq) {
             $move = \App\Models\Scm\ScmStockMove::find($request->seq);
        }

        return view('admin.scm.manage.stockmove_regist', compact('warehouses', 'move'));
    }

    // Save Stock Movement
    public function stockmove_save(Request $request)
    {
        // Validation
        $request->validate([
            'out_wh_seq' => 'required|integer',
            'in_wh_seq' => 'required|integer|different:out_wh_seq',
            'stock' => 'required|array', // [goods_seq => qty]
        ]);

        DB::beginTransaction();
        try {
            $move_code = 'M' . date('YmdHis');
            
            // 1. Create Move Master
            $move = \App\Models\Scm\ScmStockMove::create([
                'move_code' => $move_code,
                'move_status' => 2, // 2: Complete (Assuming direct move for now)
                'out_wh_seq' => $request->out_wh_seq,
                'in_wh_seq' => $request->in_wh_seq,
                'admin_memo' => $request->admin_memo,
                'total_ea' => 0,
                'regist_date' => now(),
                'complete_date' => now(), // Completed immediately
            ]);

            $total_qty = 0;
            $updated_count = 0;

            foreach ($request->stock as $goods_seq => $qty) {
                if ($qty <= 0) continue;

                // 2. Fetch Goods Info (for static log)
                $goods = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first();
                if (!$goods) continue;

                // 3. Update Location Links (The Core Logic)
                // OUT Warehouse: Decrease
                $this->updateLocationStock($request->out_wh_seq, $goods_seq, -$qty);
                
                // IN Warehouse: Increase
                $this->updateLocationStock($request->in_wh_seq, $goods_seq, $qty);

                // 4. Log Detail
                \App\Models\Scm\ScmStockMoveGoods::create([
                    'move_seq' => $move->move_seq,
                    'goods_seq' => $goods_seq,
                    'goods_name' => $goods->goods_name,
                    'goods_code' => $goods->goods_code,
                    'ea' => $qty,
                    'option_type' => 'option', // simplified
                    'option_seq' => 0,
                ]);

                $total_qty += $qty;
                $updated_count++;
            }

            if ($updated_count == 0) {
                DB::rollBack();
                return back()->with('error', '이동할 수량이 없습니다.');
            }

            $move->update(['total_ea' => $total_qty]);

            DB::commit();
            return redirect()->route('admin.scm_manage.stockmove')->with('success', '재고 이동이 완료되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '재고 이동 실패: ' . $e->getMessage());
        }
    }

    // Helper: Update Stock in Location Link
    private function updateLocationStock($wh_seq, $goods_seq, $delta) {
        $link = \App\Models\Scm\ScmLocationLink::where('wh_seq', $wh_seq)
            ->where('goods_seq', $goods_seq)
            ->first();

        if ($link) {
            $link->ea += $delta;
            // Prevent negative? Legacy allows it usually, but let's check.
            $link->save();
        } else {
            // Create new link if positive
            if ($delta > 0) {
                // Need basic goods info to create link
                $goods = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first();
                \App\Models\Scm\ScmLocationLink::create([
                    'wh_seq' => $wh_seq,
                    'goods_seq' => $goods_seq,
                    'goods_name' => $goods->goods_name,
                    'goods_code' => $goods->goods_code,
                    'option_type' => 'option',
                    'option_seq' => 0,
                    'ea' => $delta,
                    'location_code' => '', // Default
                ]);
            }
            // If delta < 0 and no link, we can theoretically create negative stock or ignore.
            // Let's create negative to track anomaly.
             else {
                 $goods = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first();
                 if ($goods) {
                    \App\Models\Scm\ScmLocationLink::create([
                        'wh_seq' => $wh_seq,
                        'goods_seq' => $goods_seq,
                        'goods_name' => $goods->goods_name,
                        'goods_code' => $goods->goods_code,
                        'option_type' => 'option',
                        'option_seq' => 0,
                        'ea' => $delta,
                        'location_code' => '',
                    ]);
                 }
             }
        }
    }
}
