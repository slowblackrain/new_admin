<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmOrderService;
use Exception;
use Illuminate\Support\Facades\DB;

class ScmOrderController extends Controller
{
    protected $scmOrderService;

    public function __construct(ScmOrderService $scmOrderService)
    {
        $this->scmOrderService = $scmOrderService;
    }

    /**
     * List SCM Orders
     * GET /admin/scm/order
     */
    public function index(Request $request)
    {
        // Fetch orders for demonstration
        $orders = DB::table('fm_scm_order')->orderByDesc('sorder_seq')->get();
        return view('admin.scm.order.index', compact('orders'));
    }

    /**
     * Create Auto Order Draft (Replicates add_auto_order_goods)
     * POST /admin/scm/auto-order
     */
    public function storeAutoOrder(Request $request)
    {
        $request->validate([
            'goods_seq' => 'required|integer',
            'order_ea' => 'required|integer',
            'option_seq' => 'required|integer',
        ]);

        try {
            // Emulate data structure required by Service
            $goodsInfo = [
                'goods_seq' => $request->goods_seq,
                'goods_name' => $request->input('goods_name', ''),
                'goods_code' => $request->input('goods_code', ''),
            ];

            $orderOption = [
                'order_seq' => $request->input('order_seq', 0),
                'order_ea' => $request->order_ea,
            ];

            $goodsOption = [
                'option_seq' => $request->option_seq,
                'option_type' => $request->input('option_type', 'option'),
                'consumer_price' => $request->input('consumer_price', 0),
                'price' => $request->input('price', 0),
                'stock' => $request->input('stock', 0),
                'badstock' => $request->input('badstock', 0),
                'safe_stock' => $request->input('safe_stock', 0),
                'reservation25' => $request->input('reservation25', 0),
                'suboption_code' => $request->input('suboption_code', ''),
                'suboption' => $request->input('suboption', ''),
                // Add loop for option1..5 if needed, assumed empty for test if not provided
            ];
            for($i=1; $i<=5; $i++) {
                $goodsOption['option'.$i] = $request->input('option'.$i, '');
                $goodsOption['optioncode'.$i] = $request->input('optioncode'.$i, '');
            }

            $result = $this->scmOrderService->createAutoOrderDraft(
                $goodsInfo, 
                $orderOption, 
                $goodsOption, 
                $request->boolean('compulsion')
            );

            if ($result) {
                return response()->json(['success' => true, 'id' => $result, 'message' => 'Auto order draft created successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Auto order condition not met (Stock sufficient).'], 200);
            }

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm selected Auto Orders (Draft -> Real Order)
     * POST /admin/scm/order/confirm
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'aoo_seqs' => 'required|array',
            'aoo_seqs.*' => 'integer'
        ]);

        try {
            $createdIds = $this->scmOrderService->confirmAutoOrders($request->aoo_seqs);
            
            if (empty($createdIds)) {
                return response()->json(['success' => false, 'message' => 'No orders created found.'], 404);
            }

            return response()->json([
                'success' => true, 
                'message' => count($createdIds) . ' Orders created successfully.',
                'order_seqs' => $createdIds
            ]);

        } catch (Exception $e) {
             return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process Warehousing (Receive Goods)
     * POST /admin/scm/order/receive
     */
    public function receive(Request $request)
    {
        $request->validate([
            'sorder_seq' => 'required|integer',
            'items' => 'required|array',
            'items.*.goods_seq' => 'required|integer',
            'items.*.option_seq' => 'required|integer',
            'items.*.ea' => 'required|integer|min:1',
        ]);

        try {
            $whsSeq = $this->scmOrderService->processWarehousing(
                $request->sorder_seq,
                $request->items
            );
            
            return response()->json([
                'success' => true, 
                'message' => 'Warehousing successful.',
                'whs_seq' => $whsSeq
            ]);

        } catch (Exception $e) {
             return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
