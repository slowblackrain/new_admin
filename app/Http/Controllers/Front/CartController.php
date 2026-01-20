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
    public function index()
    {
        $cartItems = Cart::currentUser()
            ->with(['goods.images', 'goods.option', 'options', 'inputs'])
            ->orderBy('regist_date', 'desc')
            ->get();
            
        // Valid Cart Seqs (placeholder if needed for logic, currently all)
        $validCartSeqs = $cartItems->pluck('cart_seq')->toArray();

        // Based on fm_provider_shipping (provider_seq 1, 3, etc.)
        // Default policy: 3000 won, free over 50,000 won
        $shippingCost = 3000;
        $freeShippingThreshold = 50000;
        $packagingCost = 300; // Mandatory Box Fee

        return view('front.cart.index', compact('cartItems', 'validCartSeqs', 'shippingCost', 'freeShippingThreshold', 'packagingCost'));
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
            foreach ($option_seqs as $idx => $optSeq) {
                if (empty($optSeq))
                    continue;
                $ea = $eas[$idx] ?? 1;

                // Create Cart
                $cart = new Cart();
                $cart->goods_seq = $goods_seq;
                    $cart->member_seq = Auth::check() ? Auth::id() : 0;
                // Force 0 if Auth::id() returns null unexpectedly
                if (is_null($cart->member_seq)) $cart->member_seq = 0;
                $cart->session_id = Session::getId();
                $cart->distribution = 'cart';
                $cart->regist_date = now();
                $cart->update_date = now();
                $cart->fblike = 'N';
                $cart->provider = 'N';
                $cart->ip = $request->ip();
                $cart->save();

                // Create Cart Option
                $cartOption = new CartOption();
                $cartOption->cart_seq = $cart->cart_seq;
                $cartOption->ea = $ea;

                $goodsOption = DB::table('fm_goods_option')->where('option_seq', $optSeq)->first();
                if ($goodsOption) {
                    $cartOption->option1 = $goodsOption->option1 ?? '';
                    $cartOption->option2 = $goodsOption->option2 ?? '';
                    $cartOption->option3 = $goodsOption->option3 ?? '';
                    $cartOption->option4 = $goodsOption->option4 ?? '';
                    $cartOption->option5 = $goodsOption->option5 ?? '';
                    $cartOption->title1 = $goodsOption->option_title ?? '옵션';
                    $cartOption->choice = '1';
                }
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

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => '장바구니에 담겼습니다.']);
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

            // Start: Recalculate price for return (Optional but good for UI)
            // End

            return response()->json(['status' => 'success', 'message' => '수량이 변경되었습니다.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => '변경 실패']);
        }
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
