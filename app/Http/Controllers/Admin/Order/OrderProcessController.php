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
                     // Allow cancellation from any step for now to verify stock restore logic.
                     // In real legacy this might be limited, but for stock logic verification we need this path.
                     if ($currentStep < 95) {
                         $nextStep = 95;
                         $logTitle = '주문취소';
                     }
                     break;
            }

            if ($nextStep != $currentStep) {
                // Manage Stock Transition BEFORE saving step (using currentStep)
                $this->manageStockTransition($order->order_seq, $currentStep, $nextStep);

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
            $newItem->save();

            // 4. Create New Order Item Option
            $newItemOption = new \App\Models\OrderItemOption();
            $newItemOption->item_seq = $newItem->item_seq;
            $newItemOption->order_seq = $order->order_seq;
            $newItemOption->option1 = $newOption->option1;
            $newItemOption->price = $newOption->price; 
            $newItemOption->supply_price = $newOption->supply_price ?? 0;
            $newItemOption->consumer_price = $newOption->consumer_price;
            $newItemOption->ea = 1; 
            $newItemOption->step = $targetStep;
            $newItemOption->save();

            // 5. Stock Handling (New Item - Deduct)
            // Use getStockTargets for the NEW option
            $newTargets = $this->getStockTargets($newGoods->goods_seq, $newOption, 1);
            $this->modifyStock($newTargets, $targetStep, -1); // Deduct

            // 6. Cancel Old Item
            // Mark Step 85 (Return/Exchange) 
            $originalItemOption->step = 85; 
            $originalItemOption->save();

            // 7. Stock Handling (Old Item - Restore)
            // Need to fetch original goods option to find components if package
            // But we don't have the original goodsOption object handy, we have the orderItemOption.
            // We need to look it up.
            $originalGoods = \App\Models\Goods::find($originalItemOption->item->goods_seq); // Assuming item relation
            // OR find via DB if model relation not ready
            if (!$originalGoods) {
                $itemParent = DB::table('fm_order_item')->where('item_seq', $originalItemOption->item_seq)->first();
                $originalGoodsSeq = $itemParent->goods_seq;
            } else {
                $originalGoodsSeq = $originalGoods->goods_seq;
            }
            
            // Reconstruct option keys to find GoodsOption
            $query = DB::table('fm_goods_option')->where('goods_seq', $originalGoodsSeq);
            for($i=1; $i<=5; $i++) {
                $col = "option{$i}";
                if (!empty($originalItemOption->$col)) {
                    $query->where($col, $originalItemOption->$col);
                }
            }
            $originalGoodsOption = $query->first();

            if ($originalGoodsOption) {
                 $oldTargets = $this->getStockTargets($originalGoodsSeq, $originalGoodsOption, $originalItemOption->ea);
                 $this->modifyStock($oldTargets, $targetStep, 1); // Restore (Add)
            }

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

    public function addItem(Request $request)
    {
        $request->validate([
            'order_seq' => 'required',
            'goods_seq' => 'required',
            'option_seq' => 'required',
            'ea' => 'required|integer|min:1',
        ]);

        $adminName = Auth::guard('admin')->user()->name ?? 'Admin';

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->order_seq);
            $goods = \App\Models\Goods::findOrFail($request->goods_seq);
            $option = \App\Models\GoodsOption::findOrFail($request->option_seq);
            
            // Use current order step for the new item. 
            // If order is at 15, item starts at 15 (Res15). 
            // If order is at 25, item starts at 25 (Res25).
            // If order is at 55 (Exported), item starts at 55 (Real Stock Deduct).
            $targetStep = $order->step;

            // 1. Create New Order Item (Parent)
            $newItem = new \App\Models\OrderItem();
            $newItem->order_seq = $order->order_seq;
            $newItem->goods_seq = $goods->goods_seq;
            $newItem->goods_name = $goods->goods_name;
            $newItem->goods_code = $goods->goods_code;
            $newItem->image = $goods->images->first()->image ?? ''; 
            $newItem->tax = $goods->tax;
            $newItem->shipping_policy = $goods->shipping_policy;
            $newItem->save();

            // 2. Create New Order Item Option
            $newItemOption = new \App\Models\OrderItemOption();
            $newItemOption->item_seq = $newItem->item_seq;
            $newItemOption->order_seq = $order->order_seq;
            $newItemOption->option1 = $option->option1;
            $newItemOption->price = $option->price; 
            $newItemOption->supply_price = $option->supply_price ?? 0;
            $newItemOption->consumer_price = $option->consumer_price;
            $newItemOption->ea = $request->ea; 
            $newItemOption->step = $targetStep;
            $newItemOption->save();

            // 3. Stock Handling (Deduct)
            // Use getStockTargets for the NEW option
            $targets = $this->getStockTargets($goods->goods_seq, $option, $request->ea);
            $this->modifyStock($targets, $targetStep, -1); // Deduct

            // 4. Update Order Totals (Simplified: just add price)
            // Real logic should recalculate everything, but for now we trust price update separately or implement simple add.
            $order->settleprice += ($option->price * $request->ea);
            // $order->summ_price += ($option->price * $request->ea); // Not a column
            $order->save();

            // 5. Log
            DB::table('fm_order_log')->insert([
                'order_seq' => $order->order_seq,
                'type' => 'process',
                'title' => '상품추가',
                'detail' => "{$adminName}님이 상품을 추가했습니다. [{$goods->goods_name} ({$option->option1}) x {$request->ea}]",
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

    private function manageStockTransition($orderSeq, $currentStep, $nextStep)
    {
        $items = DB::table('fm_order_item_option as o')
            ->join('fm_order_item as i', 'o.item_seq', '=', 'i.item_seq')
            ->where('o.order_seq', $orderSeq)
            ->select('o.*', 'i.goods_seq')
            ->get();

        foreach ($items as $item) {
            // Find option_seq from fm_goods_option
            $query = DB::table('fm_goods_option')->where('goods_seq', $item->goods_seq);
            for($i=1; $i<=5; $i++) {
                $col = "option{$i}";
                if (!empty($item->$col)) $query->where($col, $item->$col);
            }
            $goodsOption = $query->first();

            if (!$goodsOption) continue; 

            // Get Targets (Handles Packages)
            $targets = $this->getStockTargets($item->goods_seq, $goodsOption, $item->ea);

            // Apply logic based on transition
            // A. Deposit Confirm: 15 -> 25
            if ($currentStep == 15 && $nextStep == 25) {
                $this->modifyStock($targets, 15, -1); // Decrease Res15
                $this->modifyStock($targets, 25, 1);  // Increase Res25
            }
            
            // B. Export Complete: 45 -> 55 (Deduct Real Stock)
            if ($currentStep == 45 && $nextStep == 55) {
                $this->modifyStock($targets, 25, -1); // Decrease Res25
                $this->modifyStock($targets, 55, -1); // Decrease Stock (55+ means stock)
            }

            // C. Cancel Order: Any -> 95
            if ($nextStep == 95) {
                if ($currentStep == 15) {
                    $this->modifyStock($targets, 15, -1); // Revert Res15
                } elseif ($currentStep >= 25 && $currentStep < 55) {
                    $this->modifyStock($targets, 25, -1); // Revert Res25
                } elseif ($currentStep >= 55 && $currentStep < 95) {
                    $this->modifyStock($targets, 55, 1);  // Restore Stock
                }
            }
        }
    }

    // Helper: Resolve Stock Targets (Standard vs Package)
    private function getStockTargets($goodsSeq, $goodsOption, $itemEa)
    {
        $targets = [];

        // Check for Package
        $isPackage = false;
        for ($i = 1; $i <= 5; $i++) {
            $pkgSeqProp = "package_option_seq{$i}";
            $pkgUnitProp = "package_unit_ea{$i}";
            
            if (!empty($goodsOption->$pkgSeqProp)) {
                $isPackage = true;
                $componentOptionSeq = $goodsOption->$pkgSeqProp;
                $unitEa = !empty($goodsOption->$pkgUnitProp) ? $goodsOption->$pkgUnitProp : 1;
                $totalEa = $itemEa * $unitEa; // Total component amount for this item line

                $targets[] = ['option_seq' => $componentOptionSeq, 'ea' => $totalEa];
            }
        }

        if (!$isPackage) {
            $targets[] = ['option_seq' => $goodsOption->option_seq, 'ea' => $itemEa];
        }

        return $targets;
    }

    // Helper: Modify Stock in DB
    private function modifyStock($targets, $step, $direction)
    {
        // Direction: 1 (AddStock/Restore), -1 (DeductStock/Consume)
        // Note:
        // - Reservation Fields (15/25): "Deducting Stock" means INCREASING reservation. "Restoring Stock" means DECREASING reservation.
        // - Real Stock Field (Stock): "Deducting Stock" means DECREASING stock. "Restoring Stock" means INCREASING stock.
        
        foreach ($targets as $target) {
            $ea = $target['ea'];
            
            if ($step == 15) {
                // Reservation 15
                // Deduct (-1) -> Inc Res15
                // Restore (1) -> Dec Res15
                if ($direction < 0) DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->increment('reservation15', $ea);
                else DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->decrement('reservation15', $ea);
            } elseif ($step >= 25 && $step < 55) {
                 // Reservation 25
                 // Deduct (-1) -> Inc Res25
                 if ($direction < 0) DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->increment('reservation25', $ea);
                 else DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->decrement('reservation25', $ea);
            } elseif ($step >= 55) {
                 // Real Stock
                 // Deduct (-1) -> Dec Stock
                 // Restore (1) -> Inc Stock
                 if ($direction > 0) DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->increment('stock', $ea);
                 else DB::table('fm_goods_supply')->where('option_seq', $target['option_seq'])->decrement('stock', $ea);
            }
        }
    }
}
