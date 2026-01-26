<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Scm\ScmTrader;

class ScmOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('fm_offer as offer')
            ->join('fm_goods as g', 'offer.goods_seq', '=', 'g.goods_seq')
            ->leftJoin('fm_scm_trader as t', 'offer.trader_seq', '=', 't.trader_seq')
            ->select(
                'offer.*',
                'g.goods_name',
                'g.goods_code',
                't.trader_name'
            );

        // Filters
        if ($request->has('step') && $request->step != '') {
            $query->where('offer.step', $request->step);
        }
        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('g.goods_name', 'like', "%{$request->keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$request->keyword}%");
            });
        }
        if ($request->trader_seq) {
            $query->where('offer.trader_seq', $request->trader_seq);
        }

        $query->orderBy('offer.sno', 'desc');
        $offers = $query->paginate(20);
        $traders = ScmTrader::where('trader_use', 'Y')->get();

        return view('admin.scm.order.list', compact('offers', 'traders'));
    }

    public function update_status(Request $request)
    {
        $chk = $request->input('chk'); // Array of snos
        $action = $request->input('action'); // 'stock' (warehousing)

        if (!$chk || !is_array($chk)) {
            return back()->with('error', '선택된 항목이 없습니다.');
        }

        if ($action == 'stock') {
            DB::beginTransaction();
            try {
                foreach ($chk as $sno) {
                    $offer = DB::table('fm_offer')->where('sno', $sno)->first();
                    
                    // Only process if currently 'Ordered' (1)
                    if ($offer && $offer->step == 1) {
                        // 1. Update Offer Status
                        DB::table('fm_offer')->where('sno', $sno)->update([
                            'step' => 11, // Stocked
                            'stock_date' => now(),
                            'update_date' => now()
                        ]);

                        // 2. Increase Stock
                        DB::table('fm_goods_supply')
                            ->where('goods_seq', $offer->goods_seq)
                            ->increment('stock', $offer->ord_stock);
                        
                        // Also update total_stock just in case
                        DB::table('fm_goods_supply')
                            ->where('goods_seq', $offer->goods_seq)
                            ->increment('total_stock', $offer->ord_stock);
                    }
                }
                DB::commit();
                return back()->with('success', '선택한 발주 건이 입고 처리되었습니다.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', '처리 중 오류 발생: ' . $e->getMessage());
            }
        }

        return back()->with('error', '알 수 없는 작업입니다.');
    }
}
