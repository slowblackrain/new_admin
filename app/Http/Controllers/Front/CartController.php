<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartOption;
use App\Models\Goods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $pricingService;

    public function __construct(\App\Services\PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        $cartItems = Cart::currentUser()
            ->with(['goods.images', 'goods.option', 'options', 'inputs'])
            ->orderBy('regist_date', 'desc')
            ->get();

        // Calculate Pricing for each item
        foreach ($cartItems as $item) {
            $goods = $item->goods;
            $option = $item->options->first();
            $ea = $option->ea ?? 1;

            if ($goods && $goods->option) {
                // ... same matching logic ...
                // Optimized matching
                $matchedOption = null;
                if ($option) {
                    $matchedOption = $goods->option->first(function($o) use ($option) {
                        return (string)$o->option1 == (string)$option->option1 &&
                            (string)$o->option2 == (string)$option->option2 &&
                            (string)$o->option3 == (string)$option->option3 &&
                            (string)$o->option4 == (string)$option->option4 &&
                            (string)$o->option5 == (string)$option->option5;
                    });
                }
                $calcOption = $matchedOption ?? $goods->option->first();
            } else {
                $calcOption = null;
            }

            // Calculate
            $pricing = $this->pricingService->calculatePrice($goods, $calcOption, $ea);
            $item->pricing_info = $pricing;
            
            // Flag for Postpaid (ATS)
            // Legacy: shipping_method is in fm_cart_option
            $item->is_postpaid = false;
            if ($option && $option->shipping_method == 'postpaid') {
                $item->is_postpaid = true;
            }
        }
            
        // Valid Cart Seqs (placeholder)
        // Checkboxes logic
        $validCartSeqs = $cartItems->pluck('cart_seq')->toArray();

        // [Multi-Origin Shipping Grouping]
        $groupedCart = [];
        $totalVat = 0;
        $globalTotalProductPrice = 0;
        $globalTotalShippingCost = 0;

        $freeShippingThreshold = config('shop.shipping.free_threshold', 150000);
        $packagingCost = config('shop.shipping.packaging_cost', 300); // Mandatory Box Fee
        $baseShipping = config('shop.shipping.base_cost', 2500); // Or 3000

        foreach ($cartItems as $item) {
            if ($item->is_postpaid) {
                // Postpaid items (ATS) might be entirely separate or zero prepaid shipping
                $groupKey = 'postpaid_ats';
                $groupName = '판매대행(착불) 배송';
            } else {
                $goods = $item->goods;
                $scode = $goods->goods_scode ?? '';
                
                // Grouping Logic Based on shipping_policy
                if ($goods && $goods->shipping_policy === 'goods') {
                    // Dropship / Individual Policy
                    $prefix = substr($scode, 0, 3);
                    $groupKey = 'dropship_' . $prefix;
                    $groupName = '본사 직배송 (' . $prefix . ')';
                } else {
                    // Default Shop Policy
                    $groupKey = 'hq_default';
                    $groupName = '도매토피아 일반배송';
                }
            }

            if (!isset($groupedCart[$groupKey])) {
                $groupedCart[$groupKey] = [
                    'name' => $groupName,
                    'items' => [],
                    'total_price' => 0,
                    'shipping_cost' => 0,
                    'is_postpaid' => $item->is_postpaid
                ];
            }

            // Push Item
            $groupedCart[$groupKey]['items'][] = $item;

            // Sum product total per group
            $itemPrice = $item->pricing_info['total_price'] ?? 0;
            $groupedCart[$groupKey]['total_price'] += $itemPrice;
            $globalTotalProductPrice += $itemPrice;

            // Calculate VAT
            if ($item->goods && $item->goods->tax === 'tax') {
                $totalVat += floor($itemPrice * 0.1);
            }
        }

        // Calculate Shipping per Group
        foreach ($groupedCart as $key => &$group) {
            if ($group['is_postpaid'] || $group['total_price'] == 0) {
                // No prepay shipping if postpaid or empty
                $group['shipping_cost'] = 0;
            } else {
                // If group total is below threshold, charge base shipping once per group
                if ($group['total_price'] < $freeShippingThreshold) {
                    // Note: some dropship logic might have fixed unlimit_shipping_price, but fallback to base
                    $group['shipping_cost'] = $baseShipping;
                } else {
                    $group['shipping_cost'] = 0;
                }
            }
            $globalTotalShippingCost += $group['shipping_cost'];
        }

        return view('front.cart.index', compact('groupedCart', 'validCartSeqs', 'globalTotalProductPrice', 'globalTotalShippingCost', 'freeShippingThreshold', 'packagingCost', 'totalVat'));
    }

    /**
     * ATS (Box) Product Batch Add
     */
    public function addAtsBatch(Request $request)
    {
        $goodsSeqList = explode(',', $request->input('goods_seq_list'));
        $memberSeq = Auth::id() ?? 0; // ATS should be member only, but handle fallback
        $sessionId = Session::getId();

        DB::beginTransaction();
        try {
            foreach ($goodsSeqList as $goodsSeq) {
                if (!$goodsSeq) continue;

                $goods = Goods::find($goodsSeq);
                if (!$goods) continue;

                // 1. Get Default Option
                // ATS Box usually has 1 default option or we pick the first.
                $option = $goods->defaultOption ?? $goods->option->first();
                if (!$option) continue;

                // 2. Validate Min Purchase
                $minEa = $goods->min_purchase_ea > 0 ? $goods->min_purchase_ea : 1;
                $ea = $minEa; // ATS adds minimum required amount (usually 1 Box)

                // 3. Insert Cart
                // Check duplicate? Legacy ATS_add seems to just add or ignore. 
                // We'll use standard "Add new" for batch to be safe, or update if exists.
                // Simple update/create logic similar to store().
                
                $cart = new Cart();
                $cart->goods_seq = $goodsSeq;
                $cart->member_seq = $memberSeq;
                $cart->session_id = $sessionId;
                $cart->distribution = 'cart';
                $cart->regist_date = now();
                $cart->update_date = now();
                $cart->ip = $request->ip();
                $cart->save();

                // 4. Insert Cart Option (Postpaid Force)
                $cartOption = new CartOption();
                $cartOption->cart_seq = $cart->cart_seq;
                $cartOption->ea = $ea;
                // Force Postpaid
                $cartOption->shipping_method = 'postpaid'; 
                
                // Copy Option Titles for snapshot
                $cartOption->title1 = $option->option1 ?? '옵션';
                $cartOption->option1 = $option->option1 ?? '';
                $cartOption->option2 = $option->option2 ?? '';
                $cartOption->option3 = $option->option3 ?? '';
                $cartOption->option4 = $option->option4 ?? '';
                $cartOption->option5 = $option->option5 ?? '';
                
                $cartOption->save();
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'ATS Products added to cart.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Support both single (legacy/simple) and array (multi-option) requests
        // Transform single inputs to array for unified processing
        $goods_seq = $request->input('goods_seq');

        $option_seqs = $request->input('option_seq');
        $eas = $request->input('ea');

        if (!is_array($option_seqs)) {
            $option_seqs = [$option_seqs];
            $eas = [$eas];
        }

        $request->validate([
            'goods_seq' => 'required|exists:fm_goods,goods_seq',
            'ea.*' => 'required|integer|min:1',
            'option_seq.*' => 'nullable|exists:fm_goods_option,option_seq',
        ]);

        // Handle File Uploads (Pre-process to reuse paths)
        $mappedInputs = [];
        if ($request->has('inputs')) {
            foreach ($request->input('inputs') as $inputSeq => $inputValue) {
                $goodsInput = \App\Models\GoodsInput::find($inputSeq);
                if ($goodsInput) {
                    $mappedInputs[$inputSeq] = [
                        'type' => $goodsInput->input_form,
                        'title' => $goodsInput->input_name,
                        'value' => $inputValue,
                        'seq' => $inputSeq
                    ];
                }
            }
        }

        // Handle Files
        // Note: Files are uploaded once. We reuse the path for all items.
        // Or should we store them once and link? 
        // For simplicity, store file once, save path to all cart items.

        // First, check all potential file inputs for this product
        $productInputs = \App\Models\GoodsInput::where('goods_seq', $goods_seq)->where('input_form', 'file')->get();
        foreach ($productInputs as $pInput) {
            if ($request->hasFile("inputs.{$pInput->input_seq}")) {
                $file = $request->file("inputs.{$pInput->input_seq}");
                $filename = $file->getClientOriginalName();
                $path = $file->storeAs('uploads/order', time() . '_' . $filename, 'public');

                $mappedInputs[$pInput->input_seq] = [
                    'type' => 'file',
                    'title' => $pInput->input_name,
                    'value' => $path,
                    'seq' => $pInput->input_seq
                ];
            }
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $member_seq = $user ? $user->member_seq : 0;
            $sessionId = Session::getId();

            foreach ($option_seqs as $idx => $optSeq) {
                if (empty($optSeq))
                    continue;
                $ea = $eas[$idx] ?? 1;

                // 1. Get Option Data
                $goodsOption = DB::table('fm_goods_option')->where('option_seq', $optSeq)->first();
                if (!$goodsOption) continue;

                // 2. Check if item already exists in Cart (Same Goods + Same Options)
                // Only merge if NO custom inputs are provided (inputs make items unique)
                // 2. Check if item already exists in Cart (Same Goods + Same Options + Same Inputs)
                // We verify both options and inputs (title/value) match exactly.
                $existingCart = null;
                
                // 2. Check if item already exists in Cart (Same Goods + Same Options + Same Inputs)
                // We verify both options and inputs (title/value) match exactly.
                $existingCart = null;
                
                $query = Cart::query()
                    ->where('goods_seq', $goods_seq)
                    ->where(function($q) use ($member_seq, $sessionId) {
                        if ($member_seq > 0) {
                            $q->where('member_seq', $member_seq);
                        } else {
                            $q->where('session_id', $sessionId);
                        }
                    });

                // Eager load options AND inputs for comparison
                $candidates = $query->with(['options', 'inputs'])->get();

                foreach ($candidates as $candidate) {
                    // A. Check Options
                    $candOpt = $candidate->options->first();
                    // If no option row, treat as mismatch or empty option? 
                    // Usually goodsOption is present. If candidate has no option row, it's broken or basic.
                    // For logic safety:
                    $candOpt1 = $candOpt ? $candOpt->option1 : '';
                    $candOpt2 = $candOpt ? $candOpt->option2 : '';
                    $candOpt3 = $candOpt ? $candOpt->option3 : '';
                    $candOpt4 = $candOpt ? $candOpt->option4 : '';
                    $candOpt5 = $candOpt ? $candOpt->option5 : '';

                    $targetOpt1 = $goodsOption->option1 ?? '';
                    $targetOpt2 = $goodsOption->option2 ?? '';
                    $targetOpt3 = $goodsOption->option3 ?? '';
                    $targetOpt4 = $goodsOption->option4 ?? '';
                    $targetOpt5 = $goodsOption->option5 ?? '';

                    if (
                        (string)$candOpt1 !== (string)$targetOpt1 ||
                        (string)$candOpt2 !== (string)$targetOpt2 ||
                        (string)$candOpt3 !== (string)$targetOpt3 ||
                        (string)$candOpt4 !== (string)$targetOpt4 ||
                        (string)$candOpt5 !== (string)$targetOpt5
                    ) {
                        continue; // Options mismatch
                    }

                    // B. Check Inputs
                    // $mappedInputs: [seq => [type, title, value, ...], ...]
                    // $candidate->inputs: Collection of CartInput objects
                    
                    if ($candidate->inputs->count() !== count($mappedInputs)) {
                        continue; // Count mismatch
                    }

                    // If both empty, strict match confirmed by loop end
                    // If not empty, check every value
                    $allInputsMatch = true;
                    if (!empty($mappedInputs)) {
                        foreach ($mappedInputs as $mInput) {
                            $tTitle = $mInput['title'];
                            $tValue = $mInput['value'];
                            
                            // Find matching input in candidate
                            $found = $candidate->inputs->first(function($ci) use ($tTitle, $tValue) {
                                // Simple string comparison
                                return (string)$ci->input_title === (string)$tTitle && 
                                       (string)$ci->input_value === (string)$tValue;
                            });

                            if (!$found) {
                                $allInputsMatch = false;
                                break;
                            }
                        }
                    }
                    
                    if ($allInputsMatch) {
                        $existingCart = $candidate;
                        break;
                    }
                }

                if ($existingCart) {
                    // Update Existing
                    $cartOption = $existingCart->options->first();
                    $cartOption->ea += $ea;
                    $cartOption->save();
                    
                    // Touch parent cart to update timestamp
                    $existingCart->update_date = now();
                    $existingCart->save();

                    $createdCartSeqs[] = $existingCart->cart_seq;
                } else {
                    // Create New
                    $cart = new Cart();
                    $cart->goods_seq = $goods_seq;
                    $cart->member_seq = $member_seq; // Already resolved above
                    // Force 0 if null
                    if (is_null($cart->member_seq)) $cart->member_seq = 0;
                    
                    $cart->session_id = $sessionId;
                    $cart->distribution = 'cart';
                    $cart->regist_date = now();
                    $cart->update_date = now();
                    $cart->fblike = 'N';
                    $cart->provider = 'N';
                    $cart->ip = $request->ip();
                    $cart->save();

                    // Append to cart_seqs array for response
                    $createdCartSeqs[] = $cart->cart_seq;

                    // Create Cart Option
                    $cartOption = new CartOption();
                    $cartOption->cart_seq = $cart->cart_seq;
                    $cartOption->ea = $ea;

                    $cartOption->option1 = $goodsOption->option1 ?? '';
                    $cartOption->option2 = $goodsOption->option2 ?? '';
                    $cartOption->option3 = $goodsOption->option3 ?? '';
                    $cartOption->option4 = $goodsOption->option4 ?? '';
                    $cartOption->option5 = $goodsOption->option5 ?? '';
                    $cartOption->title1 = $goodsOption->option_title ?? '옵션';
                    $cartOption->choice = '1';
                    $cartOption->save();

                    // Save Inputs for THIS cart item
                    foreach ($mappedInputs as $mInput) {
                        $cartInput = new \App\Models\CartInput();
                        $cartInput->cart_seq = $cart->cart_seq;
                        $cartInput->cart_option_seq = $cartOption->cart_option_seq;
                        $cartInput->input_title = $mInput['title'];
                        $cartInput->type = $mInput['type'];
                        $cartInput->input_value = $mInput['value'];
                        $cartInput->save();
                    }
                }
            }

            DB::commit();

            if ($request->input('direct_buy') === 'Y') {
                $orderUrl = route('order.form_get', ['cart_seq' => $createdCartSeqs ?? []]);
                if (!Auth::check()) {
                    return redirect()->route('login', ['return_url' => $orderUrl]);
                }
                return redirect($orderUrl);
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success', 
                    'message' => '장바구니에 담겼습니다.',
                    'cart_seqs' => $createdCartSeqs ?? []
                ]);
            }
            return redirect()->route('cart.index');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                // Log error for debugging
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return back()->withErrors(['msg' => '장바구니 담기 실패']);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_seq' => 'required|exists:fm_cart,cart_seq',
            'ea' => 'required|integer|min:1',
        ]);

        try {
            $cart = Cart::currentUser()->where('cart_seq', $request->cart_seq)->firstOrFail();

            // Assuming single option per cart row for now
            $option = $cart->options->first();
            if ($option) {
                $option->ea = $request->ea;
                $option->save();
            }

            // Recalculate price for return
            $cart->load(['goods.option', 'options']);
            $goods = $cart->goods;
            $option = $cart->options->first(); // Reloaded with new EA? No, EA is in DB now.
            
            // Logic to match option again...
            if ($goods && $goods->option) {
                 $matchedOption = $goods->option->first(function($o) use ($option) {
                        return (string)$o->option1 == (string)$option->option1 &&
                            (string)$o->option2 == (string)$option->option2 &&
                            (string)$o->option3 == (string)$option->option3 &&
                            (string)$o->option4 == (string)$option->option4 &&
                            (string)$o->option5 == (string)$option->option5;
                });
                $calcOption = $matchedOption ?? $goods->option->first();
                
                $pricing = $this->pricingService->calculatePrice($goods, $calcOption, $request->ea);
                
                return response()->json([
                    'status' => 'success', 
                    'message' => '수량이 변경되었습니다.',
                    'new_unit_price' => $pricing['unit_price'],
                    'new_total_price' => $pricing['total_price']
                ]);
            }

            return response()->json(['status' => 'success', 'message' => '수량이 변경되었습니다.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => '변경 실패']);
        }
    }

    public function changeOption(Request $request)
    {
        $request->validate([
            'cart_seq' => 'required|exists:fm_cart,cart_seq',
            'option_seq' => 'required|exists:fm_goods_option,option_seq',
        ]);

        try {
            DB::beginTransaction();
            $cart = Cart::currentUser()->where('cart_seq', $request->cart_seq)->with(['options', 'inputs'])->firstOrFail();
            $newOptionInfo = DB::table('fm_goods_option')->where('option_seq', $request->option_seq)->first();

            if (!$newOptionInfo) {
                return response()->json(['status' => 'error', 'message' => '존재하지 않는 옵션입니다.']);
            }
            
            // Just update the option
            $currentOption = $cart->options->first();
            $currentOption->option1 = $newOptionInfo->option1 ?? '';
            $currentOption->option2 = $newOptionInfo->option2 ?? '';
            $currentOption->option3 = $newOptionInfo->option3 ?? '';
            $currentOption->option4 = $newOptionInfo->option4 ?? '';
            $currentOption->option5 = $newOptionInfo->option5 ?? '';
            $currentOption->title1 = $newOptionInfo->option_title ?? '옵션';
            $currentOption->save();

            $cart->update_date = now();
            $cart->save();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => '옵션이 변경되었습니다.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => '변경 실패: ' . $e->getMessage()]);
        }
    }

    public function optionalChanges(Request $request)
    {
        $cartSeq = $request->input('cart_seq');
        $cartItem = Cart::currentUser()->where('cart_seq', $cartSeq)->with(['goods', 'options', 'inputs'])->firstOrFail();
        
        $goods = $cartItem->goods;
        $options = DB::table('fm_goods_option')
            ->where('goods_seq', $goods->goods_seq)
            ->get();
            
        return view('front.cart.optional_changes', compact('cartItem', 'goods', 'options'));
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'cart_seq' => 'required|array', // Accepts array of IDs
            'cart_seq.*' => 'exists:fm_cart,cart_seq',
        ]);

        try {
            // Security: Only delete carts belonging to current user
            $validCarts = Cart::currentUser()->whereIn('cart_seq', $request->cart_seq)->get();
            $validSeq = $validCarts->pluck('cart_seq')->toArray();

            if (empty($validSeq)) {
                return response()->json(['status' => 'error', 'message' => '삭제할 상품이 없습니다.']);
            }

            // Delete Inputs
            \App\Models\CartInput::whereIn('cart_seq', $validSeq)->delete();

            // Delete Options
            CartOption::whereIn('cart_seq', $validSeq)->delete();

            // Delete Cart
            Cart::whereIn('cart_seq', $validSeq)->delete();

            return response()->json(['status' => 'success', 'message' => '삭제되었습니다.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => '삭제 실패']);
        }
    }
}
