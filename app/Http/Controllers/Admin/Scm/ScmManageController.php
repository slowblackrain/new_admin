<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScmManageController extends Controller
{
    // List Stock Revisions (History)
    public function revision(Request $request)
    {
        $query = \App\Models\Scm\ScmStockRevision::orderBy('regist_date', 'desc');

        if ($request->keyword) {
            $query->where('revision_code', 'like', "%{$request->keyword}%");
        }

        $revisions = $query->paginate(20);
        $warehouses = \App\Models\Scm\ScmWarehouse::all()->keyBy('wh_seq');

        return view('admin.scm.manage.revision', compact('revisions', 'warehouses'));
    }

    // Revision Registration Form
    public function revision_regist(Request $request)
    {
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        return view('admin.scm.manage.revision_regist', compact('warehouses'));
    }

    // Save Stock Revision
    public function revision_save(Request $request)
    {
        $request->validate([
            'wh_seq' => 'required|integer',
            'revision_type' => 'required|in:increase,decrease,set', 
            'stock' => 'required|array', // [goods_seq => qty]
        ]);

        DB::beginTransaction();
        try {
            $wh_seq = $request->wh_seq;
            $type_map = ['increase' => 1, 'decrease' => 2, 'set' => 3];
            $rev_type_int = $type_map[$request->revision_type];

            // Create Master
            $revision = \App\Models\Scm\ScmStockRevision::create([
                'revision_code' => 'R' . date('YmdHis'),
                'revision_type' => $rev_type_int,
                'revision_status' => 1, // Complete
                'wh_seq' => $wh_seq,
                'admin_memo' => $request->admin_memo,
                'total_ea' => 0,
                'complete_date' => now(),
                'regist_date' => now(),
                'krw_total_supply_price' => 0,
                'krw_total_supply_tax' => 0,
                'krw_total_price' => 0
            ]);

            $total_abs_ea = 0;
            $updated_count = 0;

            foreach ($request->stock as $goods_seq => $qty) {
                // Determine Delta
                $qty = (int)$qty;
                if ($qty == 0 && $request->revision_type != 'set') continue;

                // Lock & Get Current Stock (Local Sync)
                // Use updateLocationStock logic which handles Location Links
                // But for 'Set', we need to know current Location Stock.
                
                $current_loc_ea = 0;
                $link = \App\Models\Scm\ScmLocationLink::where('wh_seq', $wh_seq)
                        ->where('goods_seq', $goods_seq)
                        ->first();
                if ($link) $current_loc_ea = $link->ea;

                $delta = 0;
                if ($request->revision_type == 'increase') {
                    $delta = abs($qty);
                } elseif ($request->revision_type == 'decrease') {
                    $delta = -abs($qty);
                } elseif ($request->revision_type == 'set') {
                    $delta = $qty - $current_loc_ea;
                    if ($delta == 0) continue;
                }

                // Update Location Stock
                $this->updateLocationStock($wh_seq, $goods_seq, $delta);

                // Need Goods Info for Log
                // Default connection selects from Live DB or Local depending on config.
                // Since we need to join or save names, we fetch.
                $goods_info = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first();
                
                if ($goods_info) {
                    \App\Models\Scm\ScmStockRevisionGoods::create([
                        'revision_seq' => $revision->revision_seq,
                        'goods_seq' => $goods_seq,
                        'goods_name' => $goods_info->goods_name,
                        'goods_code' => $goods_info->goods_code,
                        'use_tax' => 'Y',
                        'option_type' => 'option',
                        'option_seq' => 0, 
                        'ea' => $delta, // Log the change amount? Or the target amount? Legacy 'ea' usually logged revision amount.
                        // However, for 'set', logging delta is technically correct for 'change log', 
                        // but sometimes users want to see "Changed TO X". 
                        // Let's assume 'ea' in detail table is the DELTA.
                        'supply_price' => 0,
                        'krw_supply_price' => 0,
                        'supply_tax' => 0,
                        'krw_supply_tax' => 0
                    ]);
                }

                // Verify global stock sync if needed (optional, simplistic for now)
                
                $total_abs_ea += abs($delta);
                $updated_count++;
            }

            if ($updated_count == 0) {
                DB::rollBack();
                return back()->with('warning', '변경 사항이 없습니다.');
            }

            $revision->update(['total_ea' => $total_abs_ea]);

            DB::commit();
            return redirect()->route('admin.scm_manage.revision')->with('success', '재고 조정이 완료되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '오류 발생: ' . $e->getMessage());
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
                // Default connection 'read' config -> Live DB
                $goods = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first(); 
                if (!$goods) continue;

                // 3. Update Location Links (The Core Logic)
                // ... (No change to logic)
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
                // Default connection selects from Live DB
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
                        'location_code' => '', // Default
                    ]);
                }
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
