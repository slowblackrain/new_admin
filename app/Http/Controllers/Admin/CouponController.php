<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    /**
     * 쿠폰 리스트 조회 (admin/coupon/catalog)
     */
    public function catalog(Request $request)
    {
        $query = DB::table('fm_coupon');

        // 검색 조건
        if ($request->filled('search_text')) {
            $query->where('coupon_name', 'like', '%' . $request->search_text . '%');
        }

        if ($request->filled('couponType')) {
            $query->whereIn('type', (array) $request->couponType);
        }

        if ($request->filled('use_type')) {
            $query->whereIn('use_type', (array) $request->use_type);
        }

        if ($request->filled('sdate') && $request->filled('edate')) {
            $query->whereBetween('update_date', [
                $request->sdate . ' 00:00:00',
                $request->edate . ' 23:59:59'
            ]);
        } elseif ($request->filled('sdate')) {
            $query->where('update_date', '>=', $request->sdate . ' 00:00:00');
        } elseif ($request->filled('edate')) {
            $query->where('update_date', '<=', $request->edate . ' 23:59:59');
        }

        $perPage = $request->input('perpage', 10);
        $coupons = $query->orderBy('coupon_seq', 'desc')->paginate($perPage);

        // Fetch member count for stats display
        $memberCount = DB::table('fm_member')->count();

        return view('admin.coupon.catalog', compact('coupons', 'memberCount'));
    }

    /**
     * 쿠폰 등록/수정 폼
     */
    public function regist(Request $request)
    {
        $no = $request->input('no');
        $coupon = null;
        if ($no) {
            $coupon = DB::table('fm_coupon')->where('coupon_seq', $no)->first();
        }

        return view('admin.coupon.regist', compact('coupon'));
    }

    /**
     * 쿠폰 등록/수정 처리
     */
    public function process(Request $request)
    {
        // TODO: Detailed validation depending on coupon type
        $request->validate([
            'couponName' => 'required',
            'couponType' => 'required',
            'saleType'   => 'required',
        ]);

        try {
            DB::beginTransaction();
            // Basic data mapping
            $couponData = [
                'coupon_name' => $request->input('couponName'),
                'type' => $request->input('couponType'),
                'sale_type' => $request->input('saleType'),
                'coupon_desc' => $request->input('couponDesc', ''),
                'use_type' => $request->input('coopon_usetype', 'online'),
                'update_date' => now(),
            ];

            // Add logic for percent/won
            if ($request->saleType === 'percent') {
                $couponData['percent_goods_sale'] = $request->input('percentGoodsSale', 0);
                $couponData['max_percent_goods_sale'] = $request->input('maxPercentGoodsSale', 0);
            } else {
                $couponData['won_goods_sale'] = $request->input('wonGoodsSale', 0);
            }

            if ($request->has('couponSeq') && $request->input('couponSeq')) {
                // Update
                DB::table('fm_coupon')
                    ->where('coupon_seq', $request->input('couponSeq'))
                    ->update($couponData);
                $couponSeq = $request->input('couponSeq');
            } else {
                // Insert
                $couponData['regist_date'] = now();
                $couponSeq = DB::table('fm_coupon')->insertGetId($couponData);
            }

            DB::commit();

            return redirect()->route('admin.coupon.catalog')->with('success', '쿠폰이 성공적으로 저장되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Coupon Process Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '쿠폰 저장 중 오류가 발생했습니다.');
        }
    }
}
