<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmOrderService;
use Illuminate\Support\Facades\DB;

class ScmOrderController extends Controller
{
    protected $scmOrderService;

    public function __construct(ScmOrderService $scmOrderService)
    {
        $this->scmOrderService = $scmOrderService;
    }

    /**
     * Display Order List
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['sc_sdate'] = $request->input('sc_sdate', date('Y-m-d'));
        $filters['sc_edate'] = $request->input('sc_edate', date('Y-m-d'));

        $orders = $this->scmOrderService->getOrderList($filters);
        
        return view('admin.scm.order.index', compact('orders', 'filters'));
    }

    /**
     * Show Order Form (Create/Edit)
     */
    public function create(Request $request)
    {
        $sorderSeq = $request->input('sorder_seq');
        $order = null;

        if ($sorderSeq) {
            $order = $this->scmOrderService->getOrderData($sorderSeq);
        }

        // Common Data
        $traders = DB::table('fm_scm_trader')->orderBy('trader_name')->get();
        // Currencies - Assuming standard
        $currencies = ['KRW', 'USD', 'CNY', 'JPY', 'EUR'];

        return view('admin.scm.order.form', compact('order', 'traders', 'currencies'));
    }

    /**
     * Store Order (Insert/Update)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $sorderSeq = $request->input('sorder_seq');

            $id = $this->scmOrderService->saveOrder($data, $sorderSeq);

            return redirect()->route('admin.scm_order.create', ['sorder_seq' => $id])
                             ->with('success', '발주가 저장되었습니다.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Auto Order Candidate List
     */
    public function autoOrder(Request $request)
    {
        $filters = $request->all();
        $candidates = $this->scmOrderService->getAutoOrderList($filters);

        return view('admin.scm.order.auto_order', compact('candidates', 'filters'));
    }

    /**
     * Store Auto Order Draft
     */
    public function storeAutoOrder(Request $request)
    {
        // Logic to create drafts in fm_scm_autoorder_order
        // Replicates legacy add_auto_order_goods
        try {
            $items = $request->input('items', []); // checked items
            $orderEas = $request->input('order_ea', []); // ea array keyed by option_seq

            if (empty($items)) {
                return back()->with('error', '선택된 상품이 없습니다.');
            }

            $count = 0;
            foreach ($items as $optionSeq) {
                // Fetch necessary info to mimic legacy params
                // We need goods_seq, option_seq, etc.
                // Assuming items[] contains option_seq as per view
                
                $optionInfo = DB::table('fm_goods_option as o')
                    ->join('fm_goods as g', 'o.goods_seq', '=', 'g.goods_seq')
                    ->join('fm_goods_supply as s', function($join) {
                        $join->on('g.goods_seq', '=', 's.goods_seq')
                             ->on('o.option_seq', '=', 's.option_seq');
                    })
                    ->where('o.option_seq', $optionSeq)
                    ->select(
                        'g.goods_seq', 'g.goods_name', 'g.goods_code',
                        'o.option_seq', 'o.consumer_price', 'o.price', 
                        's.stock', 's.safe_stock', 's.badstock', 's.total_stock'
                    )
                    ->first();

                if (!$optionInfo) continue;

                $ea = $orderEas[$optionSeq] ?? 0;
                if ($ea <= 0) continue;

                $goodsInfo = [
                    'goods_seq' => $optionInfo->goods_seq,
                    'goods_name' => $optionInfo->goods_name,
                    'goods_code' => $optionInfo->goods_code,
                ];
                $orderOption = [
                    'order_seq' => 0, // Default 0 for draft
                    'order_ea' => $ea,
                ];
                $goodsOption = [
                    'option_seq' => $optionInfo->option_seq,
                    'option_type' => 'option', // Assuming standard option for now
                    'consumer_price' => $optionInfo->consumer_price,
                    'price' => $optionInfo->price,
                    'stock' => $optionInfo->stock,
                    'badstock' => $optionInfo->badstock,
                    'safe_stock' => $optionInfo->safe_stock,
                    'option1' => '', // Standard options name logic if needed
                ];

                $this->scmOrderService->createAutoOrderDraft($goodsInfo, $orderOption, $goodsOption, true);
                $count++;
            }

            if ($count > 0) {
                return redirect()->route('admin.scm_order.auto_order')->with('success', "총 {$count}개의 상품이 자동발주상품에 등록되었습니다.");
            } else {
                return back()->with('error', '등록된 자동발주상품이 없습니다.');
            }

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function excel()
    {
        // TODO: Implement Excel Download if required (C-1 task checklist has it unchecked/pending)
        // Legacy analysis suggests no list excel download exists.
        return back()->with('error', '기능 준비중입니다.'); 
    }
}
