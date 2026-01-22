<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $provider = session('provider');

        // Fallback for development
        if (!$provider) {
            $provider = DB::table('fm_provider')->where('provider_id', 'dometopia001')->first();
            // Convert to array if needed, but session usually stores it as array or object
            $provider = (array) $provider;
            
            // Manually login for development
            if ($provider) {
                 $sellerModel = \App\Models\Seller::where('provider_id', 'dometopia001')->first();
                 if($sellerModel) \Illuminate\Support\Facades\Auth::guard('seller')->login($sellerModel);
            }
        }

        if (!$provider || !isset($provider['userid'])) {
            return redirect()->back()->with('error', 'Seller information not found.');
        }

        // Find linked member
        $member = Member::where('userid', $provider['userid'])->first();

        if (!$member) {
             return view('seller.order.catalog', [
                'orders' => [],
                'message' => 'Linked reseller account not found.'
            ]);
        }

        $query = Order::where('member_seq', $member->member_seq)
            ->with(['items', 'items.goods']); // Eager load items and goods

        // --- Filters ---

        // Date Range (regist_date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Status (step)
        if ($request->filled('step')) {
            $query->where('step', $request->step);
        }

        // Keyword Search
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('order_seq', 'like', "%{$keyword}%")
                  ->orWhereHas('items', function ($q2) use ($keyword) {
                      $q2->where('goods_name', 'like', "%{$keyword}%");
                  });
            });
        }

        // Default Sort
        $query->orderBy('regist_date', 'desc');

        $orders = $query->paginate(10);

        // Append query parameters to pagination links
        $orders->appends($request->all());

        return view('seller.order.catalog', compact('orders', 'provider'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $provider = session('provider');
        
        // Fallback for development
        if (!$provider) {
            $provider = DB::table('fm_provider')->where('provider_id', 'dometopia001')->first();
            $provider = (array) $provider;

            // Manually login for development
            if ($provider) {
                 $sellerModel = \App\Models\Seller::where('provider_id', 'dometopia001')->first();
                 if($sellerModel) \Illuminate\Support\Facades\Auth::guard('seller')->login($sellerModel);
            }
        }

        $member = Member::where('userid', $provider['userid'])->firstOrFail();

        // Ensure order owner matches
        $order = Order::where('order_seq', $id)
            ->where('member_seq', $member->member_seq)
            ->with(['items', 'items.options', 'items.goods'])
            ->firstOrFail();

        return view('seller.order.view', compact('order'));
    }
}
