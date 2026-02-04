<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Scm\ScmTrader;

class ScmOfferController extends Controller
{
    protected $agencySettlementService;

    public function __construct(\App\Services\Agency\AgencySettlementService $agencySettlementService)
    {
        $this->agencySettlementService = $agencySettlementService;
    }



    private function getOfferQuery(Request $request)
    {
        $query = DB::table('fm_offer as o')
            ->leftJoin('fm_goods as g', 'o.goods_seq', '=', 'g.goods_seq')
            ->leftJoin('fm_scm_order_defaultinfo as fsod', function($join) {
                $join->on('o.goods_seq', '=', 'fsod.goods_seq')
                     ->where('fsod.use_status', '=', 'Y');
            })
            ->leftJoin('fm_scm_trader as t', 'fsod.trader_seq', '=', 't.trader_seq')
            ->select(
                'o.*',
                'g.goods_name',
                'g.goods_code',
                'fsod.supply_price',
                't.trader_name',
                't.phone_number as trader_phone'
            );

        // Step (Status)
        if ($request->sch_step) {
            if ($request->sch_step == 'all') {
                // No specific step filter
            } else {
                $query->where('o.step', $request->sch_step);
            }
        }

        // Search Keyword
        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('g.goods_name', 'like', "%{$request->keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$request->keyword}%")
                  ->orWhere('o.sorder_code', 'like', "%{$request->keyword}%");
            });
        }

        // Trader
        if ($request->trader_seq) {
            $query->where('o.trader_seq', $request->trader_seq);
        }

        // Date Range
        if ($request->sc_sdate && $request->sc_edate) {
            $dateField = $request->input('sc_date_gubun', 'o.regist_date');
            $dateCol = 'o.regist_date';
            switch($dateField) {
                case 'regist_date': $dateCol = 'o.regist_date'; break;
                case 'ordering_date': $dateCol = 'o.ordering_date'; break;
                case 'cn_date': $dateCol = 'o.cn_date'; break;
                case 'end_date': $dateCol = 'o.end_date'; break;
                case 'shipment_date': $dateCol = 'o.shipment_date'; break;
            }
            $query->whereBetween($dateCol, [$request->sc_sdate . ' 00:00:00', $request->sc_edate . ' 23:59:59']);
        }

        // Sorting
        $orderBy = $request->input('orderby', 'desc@o.sno');
        $sortParts = explode('@', $orderBy);
        if (count($sortParts) == 2) {
            $query->orderBy($sortParts[1], $sortParts[0]);
        } else {
            $query->orderBy('o.sno', 'desc');
        }

        return $query;
    }

    public function index(Request $request) {
        $perPage = $request->input('perpage', 50);
        $query = $this->getOfferQuery($request);
        $offers = $query->paginate($perPage);
        $offers = $this->processOffers($offers);
        
        $traders = DB::table('fm_scm_trader')->select('trader_seq', 'trader_name')->get();
        return view('admin.scm.order.list', compact('offers', 'traders'));
    }

    public function excel(Request $request) {
        $query = $this->getOfferQuery($request);
        $offers = $query->get(); // Get check all
        
        // Transform for display using manually created collection or just passing array
        // paginate() returns LengthAwarePaginator, get() returns Collection.
        // processOffers expects iterable.
        $offers = $this->processOffers($offers);

        $filename = 'scm_order_list_' . date('YmdHis') . '.xls';
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);

        return view('admin.scm.order.excel', compact('offers'));
    }

    private function processOffers($offers) {
         foreach ($offers as $offer) {
            $offer->goods_name = $offer->goods_name; 
            $offer->is_agency = ($offer->step == '100');

             // Image URL
             if ($offer->goods_seq) {
                 $imgQuery = DB::table('fm_goods_image')
                    ->where('goods_seq', $offer->goods_seq)
                    ->where('image_type', 'thumbView')
                    ->orderBy('cut_number', 'asc')
                    ->first();
                $offer->image_url = $imgQuery ? '/data/goods/' . $imgQuery->image : '/images/no_image.gif';
            } else {
                $offer->image_url = '/images/no_image.gif';
            }

            // Background Color Logic
            $bgcolor = "#ffffff";
            switch ($offer->step) {
                case "100": $bgcolor = "#f7f0b9"; break;
                case "6": $bgcolor = "#00cc00"; break; 
                case "1": $bgcolor = ($offer->direct_chk ?? '') == 'checked' ? "#ffff00" : "#FFC4F1"; break;
                case "2": $bgcolor = "#66cc00"; break; 
                case "3": $bgcolor = "#66cc00"; break; 
                case "4": $bgcolor = "#FF9933"; break; 
                case "5": $bgcolor = "#58C2FC"; break; 
                case "7": $bgcolor = "#58C2FC"; break; 
                case "8": $bgcolor = "#AAFFAE"; break; 
                case "9": $bgcolor = "#35a160"; break; 
                case "10": $bgcolor = "#ffff00"; break; 
                case "11": $bgcolor = "#EEEEEE"; break; 
                case "12": $bgcolor = "#CCCCCC"; break; 
                case "13": $bgcolor = "#CCCCCC"; break; 
                case "14": $bgcolor = "#ccccff"; break; 
            }
            $offer->bgcolor = $bgcolor;

            // Step Text
            switch($offer->step){

                case "100": $offer->step_text = "대행"; break;
                case "6": $offer->step_text = "발대"; break;
                case "1": $offer->step_text = "주문"; break;
                case "2": $offer->step_text = "가입고"; break; // Or "시장주문"? Legacy says "가입고" in switch, but "시장주문" in another? Line 578 says "가입고".
                case "3": $offer->step_text = "재조사"; break;
                case "4": $offer->step_text = "중입예"; break;
                case "5": $offer->step_text = "중입-"; break;
                case "7": $offer->step_text = "중입+"; break;
                case "8": $offer->step_text = "선적"; break;
                case "9": $offer->step_text = "선대기"; break;
                case "10": $offer->step_text = "한입예"; break;
                case "11": $offer->step_text = "한입고"; break;
                case "12": $offer->step_text = "한반품"; break;
                case "13": $offer->step_text = "중반품"; break;
                case "14": $offer->step_text = "중재고"; break;
                default: $offer->step_text = $offer->step;
            }

            // Calculate ship_in_total from ord_total (Legacy Logic)
            $offer->ship_in_total = 0;
            if (!empty($offer->ord_total)) {
                $ord_total_arr = explode("|", $offer->ord_total);
                // Filter empty values and get last one
                $filtered = array_filter($ord_total_arr, function($v) { return $v !== ''; });
                $offer->ship_in_total = end($filtered) ?: 0;
            }

            // Format Prices if null
            $offer->supply_price = $offer->supply_price ?? 0;
            $offer->ord_shipping = $offer->ord_shipping ?? 0;
            $offer->tot_price = $offer->tot_price ?? 0;
        }

        return $offers;
    }

    public function update_status(Request $request) {
        $action = $request->input('action');
        $chk = $request->input('chk', []);

        if (empty($chk)) {
             return back()->with('error', '선택된 항목이 없습니다.');
        }

        DB::beginTransaction();
        try {
            if ($action == 'cancel') {
                foreach ($chk as $sno) {
                    $offer = DB::table('fm_offer')->where('sno', $sno)->first();
                    if (!$offer) continue;

                    // Agency Refund Logic
                    if ($offer->step == 100 || ($offer->provider_chk ?? '') == 'checked') {
                         $goods = DB::table('fm_goods')->where('goods_seq', $offer->goods_seq)->first();
                         if ($goods && $goods->provider_seq > 1) {
                             $provider = DB::table('fm_provider')->where('provider_seq', $goods->provider_seq)->first();
                             if ($provider && $provider->userid) {
                                  $member = DB::table('fm_member')->where('userid', $provider->userid)->first();
                                  if ($member) {
                                      // Calculate Refund Amount
                                      // Logic: Supply Price * Qty + Shipping
                                      // Using Legacy Qty Logic from detail method
                                      $qty = 0;
                                      if (!empty($offer->ord_total)) {
                                          $ord_total_arr = explode("|", $offer->ord_total);
                                          $filtered = array_filter($ord_total_arr, function($v) { return $v !== ''; });
                                          $qty = end($filtered) ?: 0;
                                      }
                                      
                                      // Get Supply Price (Try offer's own logic or default info)
                                      // Since fm_offer doesn't strictly have supply_price column usage is messy,
                                      // We try to get it from defaultinfo logic or goods.
                                      // For Agency, it's usually defined in fm_scm_order_defaultinfo.
                                      $supplyPrice = 0;
                                      $defaultInfo = DB::table('fm_scm_order_defaultinfo')
                                          ->where('goods_seq', $offer->goods_seq)
                                          ->where('use_status', 'Y')
                                          ->first();
                                      
                                      if ($defaultInfo) {
                                          $supplyPrice = $defaultInfo->supply_price;
                                      }
                                      
                                      $shipping = $offer->ord_shipping ?? 0;
                                      $refundAmount = ($supplyPrice * $qty) + $shipping;

                                      if ($refundAmount > 0) {
                                          $this->agencySettlementService->refundAgencyCash($sno, $member->member_seq, $refundAmount);
                                      }
                                  }
                             }
                         }
                    }

                    DB::table('fm_offer')->where('sno', $sno)->delete();
                }
                $msg = '발주가 삭제(취소)되었습니다.';
            } 
            elseif ($action == 'status_1') {
                 // Change to "Order" (Step 1)
                 DB::table('fm_offer')->whereIn('sno', $chk)->update([
                     'step' => 1, 
                     'order_date' => now()
                 ]);
                 $msg = '발주(주문) 상태로 변경되었습니다.';
            }
            elseif ($action == 'status_11') {
                 // Change to "Stocked" (Han-ip-go Step 11)
                 DB::table('fm_offer')->whereIn('sno', $chk)->update([
                     'step' => 11,
                     'cn_date' => now() // Assuming Korea Warehousing Date
                 ]);
                 $msg = '입고완료 상태로 변경되었습니다.';
            }
            elseif ($action == 'soldout') {
                 // Soldout Logic (Updates fm_goods)
                 foreach ($chk as $sno) {
                     $offer = DB::table('fm_offer')->where('sno', $sno)->first();
                     if (!$offer || !$offer->goods_seq) continue;

                     $goods = DB::table('fm_goods')->where('goods_seq', $offer->goods_seq)->first();
                     if ($goods) {
                         // Remove 'runout_order' and ensure 'soldout' is present
                         $statusInfo = str_replace(['runout_order,', 'soldout,'], '', $goods->goods_status_info);
                         $statusInfo .= 'soldout,'; // Append soldout

                         DB::table('fm_goods')->where('goods_seq', $offer->goods_seq)->update([
                             'goods_status_info' => $statusInfo,
                             'runout_policy' => 'ableStock',
                             'able_stock_limit' => 0
                         ]);
                     }
                 }
                 $msg = '선택한 상품이 단종 처리되었습니다.';
            }
            
            DB::commit();
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    public function updateField(Request $request)
    {
        $request->validate([
            'sno' => 'required|integer',
            'field' => 'required|string',
            'value' => 'nullable',
        ]);

        $sno = $request->sno;
        $field = $request->field;
        $value = $request->value;

        // Map frontend field names to DB columns
        $dbField = $field;
        $table = 'fm_offer';
        $recalcTotal = false;

        switch ($field) {
            case 'regist_date':
            case 'ordering_date':
            case 'cn_date':
            case 'shipment_date':
                if ($value && !strtotime($value)) return response()->json(['error' => 'Invalid date'], 400);
                break;
            
            case 'supply_price':
                 $table = 'fm_scm_order_defaultinfo';
                 $offer = DB::table('fm_offer')->where('sno', $sno)->first();
                 if ($offer) {
                     // Update or Create master price entry
                     // Assuming trader_seq = 1 (Default) if not found or linked
                     // Ideally we should find the correct trader_seq from fm_scm_trader group 1
                     // But for now, we use updateOrInsert on goods_seq.
                     // IMPORTANT: If duplicate goods_seq exists with different trader, this might be ambiguous.
                     // We'll target the one for this goods.
                     
                     // Check if ANY entry exists for this goods
                     $exists = DB::table($table)->where('goods_seq', $offer->goods_seq)->exists();
                     
                     if ($exists) {
                         DB::table($table)->where('goods_seq', $offer->goods_seq)->limit(1)->update(['supply_price' => $value]);
                     } else {
                         // Create new entry
                         DB::table($table)->insert([
                             'goods_seq' => $offer->goods_seq,
                             'trader_seq' => 1, // Default Trader
                             'supply_price' => $value,
                             'regist_date' => now()
                         ]);
                     }
                     return response()->json(['success' => true]);
                 }
                 return response()->json(['error' => 'Offer not found'], 404);
            
            case 'scm_memo':
                 $offer = DB::table('fm_offer')->where('sno', $sno)->first();
                 if ($offer && $offer->goods_seq) {
                     DB::table('fm_goods')->where('goods_seq', $offer->goods_seq)->update(['scm_memo' => $value]);
                     return response()->json(['success' => true]);
                 }
                 return response()->json(['error' => 'Goods not found'], 404);

            case 'ord_shipping':
            case 'ship_in_total':
                 $recalcTotal = true;
                 break;
        }

        DB::table($table)->where('sno', $sno)->update([$dbField => $value]);

        // Recalculate Total if needed (Simple approximation for now)
        if ($recalcTotal) {
            $offer = DB::table('fm_offer')->where('sno', $sno)->first();
            // Assuming tot_price = supply_price + ord_shipping (per unit or total?)
            // Legacy uses supply_price * ship_in_total ??
            // Let's defer complex calc until requested, but update if fields change.
            // For now, let's just save the value.
            // If user enters 'supply_price', 'ord_shipping', we just save it.
            // 'tot_price' is usually calculated.
        }

        return response()->json(['success' => true]);
    }

    public function detail(Request $request) {
        $sno = $request->sno;
        
        $offer = DB::table('fm_offer as o')
            ->leftJoin('fm_goods as g', 'o.goods_seq', '=', 'g.goods_seq')
             ->leftJoin('fm_scm_order_defaultinfo as fsod', function($join) {
                $join->on('o.goods_seq', '=', 'fsod.goods_seq')
                     ->where('fsod.use_status', '=', 'Y');
            })
            ->leftJoin('fm_scm_trader as t', 'fsod.trader_seq', '=', 't.trader_seq')
            ->select(
                'o.*',
                'g.goods_name',
                'g.goods_code',
                'g.scm_memo', // SCM Memo from Goods
                'fsod.supply_price',
                't.trader_name',
                't.phone_number as trader_phone'
            )
            ->where('o.sno', $sno)
            ->first();

        if (!$offer) return '<div class="alert alert-danger">발주 정보를 찾을 수 없습니다.</div>';

        // Image
        if ($offer->goods_seq) {
             $imgQuery = DB::table('fm_goods_image')
                ->where('goods_seq', $offer->goods_seq)
                ->where('image_type', 'thumbView')
                ->orderBy('cut_number', 'asc')
                ->first();
            $offer->image_url = $imgQuery ? '/data/goods/' . $imgQuery->image : '/images/no_image.gif';
        } else {
            $offer->image_url = '/images/no_image.gif';
        }
        
        // Status Text
        switch($offer->step){
            case "100": $offer->step_text = "대행"; break;
            case "6": $offer->step_text = "발대"; break;
            case "1": $offer->step_text = "주문"; break;
            case "2": $offer->step_text = "가입고"; break;
            case "11": $offer->step_text = "한입고"; break;
            case "15": $offer->step_text = "삭제"; break;
            default: $offer->step_text = $offer->step;
        }

        // Calculate ship_in_total (Legacy Logic)
        $offer->ship_in_total = 0;
        if (!empty($offer->ord_total)) {
            $ord_total_arr = explode("|", $offer->ord_total);
            $filtered = array_filter($ord_total_arr, function($v) { return $v !== ''; });
            $offer->ship_in_total = end($filtered) ?: 0;
        }
        
        // Ensure numeric for formatting
        $offer->supply_price = $offer->supply_price ?? 0;
        $offer->ord_shipping = $offer->ord_shipping ?? 0;
        $offer->tot_price = $offer->tot_price ?? 0;

        return view('admin.scm.order.detail_modal', compact('offer'));
    }
}
