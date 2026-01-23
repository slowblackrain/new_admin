<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderProcessController extends Controller
{
    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_seq' => 'required',
            'mode' => 'required', // 'deposit_confirm', 'prepare_goods', 'export_goods', 'cancel_order'
        ]);

        $adminName = Auth::guard('admin')->user()->name ?? 'Admin';
        
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->order_seq);
            $currentStep = $order->step;
            $nextStep = $currentStep;
            $logTitle = '';
            
            // Logic based on mode
            switch ($request->mode) {
                case 'deposit_confirm':
                    if ($currentStep == 15) {
                        $nextStep = 25;
                        $logTitle = '입금확인';
                        $order->deposit_yn = 'Y';
                        $order->deposit_date = now();
                    }
                    break;
                case 'prepare_goods':
                    if ($currentStep == 25) {
                        $nextStep = 45;
                        $logTitle = '상품준비중 처리';
                    }
                    break;
                case 'export_goods': // 출고완료
                    if ($currentStep == 45) {
                        $nextStep = 55;
                        $logTitle = '배송처리';
                        // Validate delivery info if needed
                    }
                    break;
                case 'cancel_order': // 주문취소
                     // Restore Stock logic needed here or simple step change?
                     // Verify if stock needs restore
                     if ($currentStep < 55) {
                         // Restore reserved stock
                         // Logic pending: Iterate items and restore
                         // For MVP, just Change Step to 95 and let Admin handle stock manually or impl logic later
                         $nextStep = 95;
                         $logTitle = '주문취소';
                     }
                     break;
            }

            if ($nextStep != $currentStep) {
                $order->step = $nextStep;
                $order->save();

                // Update Items steps? Update OrderItemOption where step exists
                \App\Models\OrderItemOption::where('order_seq', $order->order_seq)->update(['step' => $nextStep]);

                // Log
                DB::table('fm_order_log')->insert([
                    'order_seq' => $order->order_seq,
                    'type' => 'process',
                    'title' => $logTitle,
                    'detail' => "{$adminName}님이 {$logTitle} 처리했습니다.",
                    'regist_date' => now()
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function replaceItem(Request $request)
    {
        $request->validate([
            'order_seq' => 'required',
            'original_item_seq' => 'required',
            'new_goods_seq' => 'required',
            'new_option_seq' => 'required',
            'change_code' => 'required',
        ]);

        $adminName = Auth::guard('admin')->user()->name ?? 'Admin';

        DB::beginTransaction();
        try {
            // 1. Fetch Original Item & Order
            // Find logic might need adjustment if item_seq is from fm_order_item_option
            // But usually UI passes option item seq. Let's assume input is item_seq of fm_order_item or fm_order_item_option.
            // Based on legacy, we operate on options.
            $originalItemOption = \App\Models\OrderItemOption::findOrFail($request->original_item_seq);
            $order = Order::findOrFail($request->order_seq);
            $targetStep = $originalItemOption->step; 

            // 2. Fetch New Product Info
            $newGoods = \App\Models\Goods::findOrFail($request->new_goods_seq);
            $newOption = \App\Models\GoodsOption::findOrFail($request->new_option_seq);

            // 3. Create New Order Item (Parent)
            $newItem = new \App\Models\OrderItem();
            $newItem->order_seq = $order->order_seq;
            $newItem->goods_seq = $newGoods->goods_seq;
            $newItem->goods_name = $newGoods->goods_name;
            $newItem->goods_code = $newGoods->goods_code;
            $newItem->image = $newGoods->images->first()->image ?? ''; 
            $newItem->tax = $newGoods->tax;
            $newItem->shipping_policy = $newGoods->shipping_policy;
            // $newItem->regist_date = now(); // Column does not exist
            $newItem->save();

            // 4. Create New Order Item Option
            $newItemOption = new \App\Models\OrderItemOption();
            $newItemOption->item_seq = $newItem->item_seq;
            $newItemOption->order_seq = $order->order_seq;
            // $newItemOption->goods_seq = $newGoods->goods_seq; // Column does not exist
            // $newItemOption->option_seq = $newOption->option_seq; // Column does not exist
            $newItemOption->option1 = $newOption->option1;
            // Use existing price logic or new price? Defaulting to new price.
            $newItemOption->price = $newOption->price; 
            $newItemOption->supply_price = $newOption->supply_price ?? 0;
            $newItemOption->consumer_price = $newOption->consumer_price;
            $newItemOption->ea = 1; 
            $newItemOption->step = $targetStep;
            // $newItemOption->regist_date = now(); // Column does not exist
            $newItemOption->save();

            // 5. Stock Handling (New Item - Deduct)
            $this->updateStock($newOption->option_seq, $targetStep, 'deduct');

            // 6. Cancel Old Item
            // Mark Step 85 (Return/Exchange) 
            $originalItemOption->step = 85; 
            $originalItemOption->save();

            // 7. Stock Handling (Old Item - Restore)
             $this->updateStock($originalItemOption->option_seq, $targetStep, 'restore');

            // 8. Log
            DB::table('fm_order_log')->insert([
                'order_seq' => $order->order_seq,
                'type' => 'process',
                'title' => '상품교환',
                'detail' => "{$adminName}님이 상품을 교환했습니다. (사유: {$request->change_code}) [{$originalItemOption->goods_seq} -> {$newGoods->goods_seq}]",
                'regist_date' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'order_seq' => 'required',
            'change_type' => 'required|in:price_info',
            'settleprice' => 'numeric',
            'emoney' => 'numeric',
            'shipping_cost' => 'numeric',
            'coupon_sale' => 'numeric',
            'admin_memo' => 'nullable|string',
        ]);

        $adminName = Auth::guard('admin')->user()->name ?? 'Admin';

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->order_seq);

            // Log changes
            $changes = [];
            if ($request->has('settleprice') && $order->settleprice != $request->settleprice) {
                $changes[] = "결제금액: " . number_format($order->settleprice) . " -> " . number_format($request->settleprice);
                $order->settleprice = $request->settleprice;
            }
            if ($request->has('emoney') && $order->emoney != $request->emoney) {
                $changes[] = "적립금사용: " . number_format($order->emoney) . " -> " . number_format($request->emoney);
                $order->emoney = $request->emoney;
            }
            if ($request->has('shipping_cost') && $order->shipping_cost != $request->shipping_cost) {
                $changes[] = "배송비: " . number_format($order->shipping_cost) . " -> " . number_format($request->shipping_cost);
                $order->shipping_cost = $request->shipping_cost;
            }
            if ($request->has('coupon_sale') && $order->coupon_sale != $request->coupon_sale) {
                $changes[] = "쿠폰할인: " . number_format($order->coupon_sale) . " -> " . number_format($request->coupon_sale);
                $order->coupon_sale = $request->coupon_sale;
            }

            if (empty($changes)) {
                return response()->json(['success' => false, 'message' => '변경된 내용이 없습니다.']);
            }

            $order->save();

            // Log
            $memo = "{$adminName}님이 주문금액을 수정했습니다. " . implode(", ", $changes);
            if ($request->admin_memo) {
                $memo .= " (사유: {$request->admin_memo})";
            }

            DB::table('fm_order_log')->insert([
                'order_seq' => $order->order_seq,
                'type' => 'process',
                'title' => '금액수정',
                'detail' => $memo,
                'regist_date' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function updateStock($optionSeq, $step, $type)
    {
        // Simple Stock Logic based on Legacy
        // Type: 'deduct' (New Item), 'restore' (Old Item)
        $supply = DB::table('fm_goods_supply')->where('option_seq', $optionSeq);
        if (!$supply->exists()) return;

        if ($type === 'deduct') {
            if ($step == 15) {
                $supply->increment('reservation15');
            } elseif ($step >= 25 && $step < 55) {
                $supply->increment('reservation25');
            } elseif ($step >= 55) {
                $supply->decrement('stock');
            }
        } else { // restore
            if ($step == 15) {
                $supply->decrement('reservation15');
            } elseif ($step >= 25 && $step < 55) {
                $supply->decrement('reservation25');
            } elseif ($step >= 55) {
                $supply->increment('stock');
            }
        }
    }
}
