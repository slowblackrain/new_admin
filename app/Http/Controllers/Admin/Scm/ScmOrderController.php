<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Scm\ScmTrader;

use App\Services\Agency\AgencySettlementService;

class ScmOrderController extends Controller
{
    protected $agencySettlementService;

    public function __construct(AgencySettlementService $agencySettlementService)
    {
        $this->agencySettlementService = $agencySettlementService;
    }

    // Auto Order List (Balju Request)
    public function auto_order(Request $request)
    {
        // 1. Calculate Required Quantity based on Orders
        // Orders: Payment Confirmed (step >= 25) AND Not Shipped (step < 75)
        // Corrected to sum from order_item_option
        $required = DB::table('fm_order_item as item')
            ->join('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->join('fm_order as ord', 'item.order_seq', '=', 'ord.order_seq')
            ->select(
                'item.goods_seq',
                DB::raw('SUM(opt.ea) as required_qty')
            )
            ->where('ord.step', '>=', 25) 
            ->where('ord.step', '<', 75)
            ->groupBy('item.goods_seq');

        // 2. Join with Goods Info, Current Stock, and Supplier Info
        // Using Left Join for Supplier because some might not be mapped
        $query = DB::table('fm_goods as g')
            ->joinSub($required, 'req', function ($join) {
                $join->on('g.goods_seq', '=', 'req.goods_seq');
            })
            ->leftJoin('fm_goods_supply as sup', 'g.goods_seq', '=', 'sup.goods_seq')
            ->leftJoin('fm_scm_order_defaultinfo as def', function($join){
                $join->on('g.goods_seq', '=', 'def.goods_seq')
                     ->where('def.main_trade_type', 'Y');
            })
            ->leftJoin('fm_scm_trader as t', 'def.trader_seq', '=', 't.trader_seq')
            ->select(
                'g.goods_seq',
                'g.goods_name',
                'g.goods_code',
                'g.regist_date',
                'g.provider_seq',
                'req.required_qty',
                DB::raw('IFNULL(sup.stock, 0) as current_stock'),
                't.trader_name',
                't.trader_seq'
            );

        // Apply Search Filters
        if ($request->keyword) {
            $query->where('g.goods_name', 'like', "%{$request->keyword}%");
        }
        if ($request->trader_seq) {
            $query->where('t.trader_seq', $request->trader_seq);
        }

        $items = $query->paginate(50);
        $traders = ScmTrader::where('trader_use', 'Y')->get();

        return view('admin.scm.order.auto', compact('items', 'traders'));
    }

    public function create_auto_order(Request $request)
    {
        $orders = $request->input('orders'); // Array of [goods_seq => qty]
        
        if (!$orders || !is_array($orders)) {
            return back()->with('error', '선택된 상품이 없습니다.');
        }

        DB::beginTransaction();
        try {
            $sorder_seq = time(); // Simple Batch ID
            $success_count = 0;
            $fail_count = 0;
            $fail_messages = [];

            foreach ($orders as $goods_seq => $qty) {
                if ($qty <= 0) continue;

                $goods = DB::table('fm_goods')->where('goods_seq', $goods_seq)->first();
                if (!$goods) continue;

                // 1. Get Trader Info
                $defaultInfo = DB::table('fm_scm_order_defaultinfo')
                    ->where('goods_seq', $goods_seq)
                    ->where('main_trade_type', 'Y')
                    ->first();
                $trader_seq = $defaultInfo ? $defaultInfo->trader_seq : 0;
                $supply_price = $defaultInfo ? $defaultInfo->supply_price : 0;

                // Fallback supply price if 0 (approx from consumer price)
                if ($supply_price == 0) {
                     $option = DB::table('fm_goods_option')->where('goods_seq', $goods_seq)->where('default_option', 'y')->first();
                     if($option) $supply_price = $option->consumer_price * 0.9;
                }

                // 2. Check Agency Validation (Provider Cache)
                // [Modified] Double Charging Prevention:
                // Agency Cash is already deducted at Front/OrderController when order is placed.
                // Therefore, we DO NOT deduct again here at Auto-Order creation.
                /*
                if ($goods->provider_seq > 1) { // 1 is Admin, >1 is Seller
                    // ...
                    try {
                        $this->agencySettlementService->deductAgencyCash(
                            $sorder_seq,
                            $goods->provider_seq,
                            $total_cost
                        );
                    } catch (\Exception $e) {
                         // ...
                    }
                }
                */

                // 3. Create Offer (Balju)
                DB::table('fm_offer')->insert([
                    'sorder_seq' => $sorder_seq,
                    'goods_seq' => $goods_seq,
                    'trader_seq' => $trader_seq,
                    'step' => 1, // Order Placed
                    'ord_stock' => $qty,
                    'ord_date' => now(), // Simple Date
                    'order_date' => now(),
                    'regist_date' => now(),
                    'update_date' => now(),
                    'offer_cn' => '', // Init
                    'visitant' => 'Auto',
                    'offer_box' => 0,
                    'ord_total' => $qty . '|0|0', // Init format
                    'provider_chk' => ($goods->provider_seq > 1) ? 'checked' : 'none'
                ]);

                $success_count++;
            }

            DB::commit();

            $msg = "발주 $success_count 건 생성 완료.";
            if ($fail_count > 0) {
                $msg .= " 실패 $fail_count 건 (" . implode(", ", $fail_messages) . ")";
                return redirect()->route('admin.scm_order.auto')->with('warning', $msg);
            }

            return redirect()->route('admin.scm_order.auto')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '발주 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
