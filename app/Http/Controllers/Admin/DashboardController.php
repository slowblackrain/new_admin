<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $searchDate = $request->input('search_date', date('Y-m-d'));
        $show30Days = $request->has('show_30_days');
        $compareYear = $request->has('compare_year');
        
        $endDate = $searchDate;
        $startDate = $show30Days 
            ? Carbon::parse($searchDate)->subDays(29)->format('Y-m-d')
            : Carbon::parse($searchDate)->subDays(9)->format('Y-m-d');

        $data = $this->getDashboardData($startDate, $endDate);

        if ($compareYear) {
            $priorStartDate = Carbon::parse($startDate)->subWeeks(52)->format('Y-m-d');
            $priorEndDate = Carbon::parse($endDate)->subWeeks(52)->format('Y-m-d');
            $priorData = $this->getDashboardData($priorStartDate, $priorEndDate);

            // Merge Prior Data
            $dates = array_keys($data);
            $priorDates = array_keys($priorData);

            foreach ($dates as $i => $date) {
                if (isset($priorDates[$i])) {
                    $pDate = $priorDates[$i];
                    foreach ($data[$date] as $metric => $values) {
                        $curr = $values['amount'];
                        $prior = $priorData[$pDate][$metric]['amount'] ?? 0;
                        
                        // Legacy Formula: 100 + ((Curr - Prior) / Prior * 100) => (Curr / Prior) * 100
                        $diff = 0;
                        if ($prior > 0) {
                            $diff = ($curr / $prior) * 100;
                        } elseif ($curr > 0) {
                            $diff = 100; // New sales where none existed? Legacy behavior unknown, assuming 100% or just show raw
                        }

                        $data[$date][$metric]['prior_amount'] = $prior;
                        $data[$date][$metric]['ratio'] = round($diff);
                    }
                }
            }
        }

        // SCM Stats
        $scmStats = [
            'order_today' => 0,
            'shipping_prep' => 0,
            'offer_waiting' => 0,
            'stocked_today' => 0
        ];

        // Today's Orders (Step 25~75, Mode!=XMAS)
        $scmStats['order_today'] = DB::table('fm_order')
            ->where('regist_date', '>=', date('Y-m-d 00:00:00'))
            ->where('mode', '!=', 'XMAS')
            ->count();

        // Shipping Prep (Step 25, 35, 45?) - Assuming 25 is 'Payment Confirmed' or 'Preparing'
        $scmStats['shipping_prep'] = DB::table('fm_order')
            ->whereIn('step', ['25', '35']) // Adjust steps based on legacy logic
            ->count();

        // Offer Waiting (Step < 11) using SCM tables
        $scmStats['offer_waiting'] = DB::table('fm_offer')
            ->where('step', '<', 11)
            ->count();
        
        // Stocked Today (Step 11, Stock Date Today)
        $scmStats['stocked_today'] = DB::table('fm_offer')
            ->where('step', 11)
            ->where('stock_date', 'like', date('Y-m-d').'%')
            ->count();

        return view('admin.dashboard', compact('data', 'searchDate', 'show30Days', 'compareYear', 'scmStats'));
    }

    private function getDashboardData($startDate, $endDate)
    {
        $cfg = $this->getMarketingConfig();
        $dates = [];
        $curr = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($curr <= $end) {
            $dates[$curr->format('Y-m-d')] = [
                // Core
                'retail' => ['amount' => 0, 'count' => 0],
                'wholesale' => ['amount' => 0, 'count' => 0],
                'b2b_shipping' => ['amount' => 0, 'count' => 0],
                'b2b_affiliate' => ['amount' => 0, 'count' => 0],
                'b2b_promo' => ['amount' => 0, 'count' => 0],
                'startup' => ['amount' => 0, 'count' => 0],
                'linked' => ['amount' => 0, 'count' => 0],
                'overseas' => ['amount' => 0, 'count' => 0],
                'open_market' => ['amount' => 0, 'count' => 0],
                'rocket' => ['amount' => 0, 'count' => 0],
                'general_mall' => ['amount' => 0, 'count' => 0],
                
                // Calculated / Ad Headers
                'retail_wholesale_sum' => ['amount' => 0, 'count' => 0],
                'retail_ad_cost' => ['amount' => 0],
                'retail_ad_ratio' => ['amount' => 0],
                
                'doto_total' => ['amount' => 0, 'count' => 0], // Retail...Startup
                'marketing_total' => ['amount' => 0, 'count' => 0], // Open...General
                'marketing_ad_cost' => ['amount' => 0],
                'marketing_ad_ratio' => ['amount' => 0],
                
                'grand_total' => ['amount' => 0, 'count' => 0],
            ];
            $curr->addDay();
        }

        // Helper to merge results
        $merge = function($type, $results) use (&$dates) {
            foreach ($results as $row) {
                if (isset($dates[$row->date])) {
                    $dates[$row->date][$type] = [
                        'amount' => $row->total_amount,
                        'count' => $row->total_count
                    ];
                }
            }
        };

        // 1. Fetch Sales Metrics
        $merge('retail', $this->querySales($startDate, $endDate, $cfg, 'retail'));
        $merge('wholesale', $this->querySales($startDate, $endDate, $cfg, 'wholesale'));
        $merge('b2b_shipping', $this->querySales($startDate, $endDate, $cfg, 'b2b_shipping'));
        $merge('b2b_affiliate', $this->querySales($startDate, $endDate, $cfg, 'b2b_affiliate'));
        $merge('b2b_promo', $this->querySales($startDate, $endDate, $cfg, 'b2b_promo'));
        $merge('startup', $this->querySales($startDate, $endDate, $cfg, 'startup'));
        $merge('linked', $this->querySales($startDate, $endDate, $cfg, 'linked'));
        $merge('overseas', $this->querySales($startDate, $endDate, $cfg, 'overseas'));
        $merge('open_market', $this->querySales($startDate, $endDate, $cfg, 'open_market'));
        $merge('rocket', $this->querySales($startDate, $endDate, $cfg, 'rocket'));
        $merge('general_mall', $this->querySales($startDate, $endDate, $cfg, 'general_mall'));

        // 2. Fetch Ad Costs
        // Retail Ad (gubun='use')
        $retailAds = DB::connection('mysql')->table('fm_marketing_advertising')
            ->selectRaw('in_date as date, sum(ad_price) as cost')
            ->where('gubun', 'use')
            ->whereBetween('in_date', [$startDate, $endDate])
            ->groupBy('in_date')
            ->get();
            
        foreach ($retailAds as $ad) {
            if (isset($dates[$ad->date])) {
                $dates[$ad->date]['retail_ad_cost']['amount'] = (float) $ad->cost;
            }
        }

        // Marketing Ads (Open=use2, Rocket=use5, General=use6)
        $marketingAds = DB::connection('mysql')->table('fm_marketing_advertising')
            ->selectRaw('in_date as date, sum(ad_price) as cost')
            ->whereIn('gubun', ['use2', 'use5', 'use6'])
            ->whereBetween('in_date', [$startDate, $endDate])
            ->groupBy('in_date')
            ->get();

        foreach ($marketingAds as $ad) {
            if (isset($dates[$ad->date])) {
                $dates[$ad->date]['marketing_ad_cost']['amount'] = (float) $ad->cost;
            }
        }

        // 3. Calculate Subtotals & Ratios
        foreach ($dates as &$day) {
            // Retail + Wholesale
            $day['retail_wholesale_sum']['amount'] = $day['retail']['amount'] + $day['wholesale']['amount'];
            $day['retail_wholesale_sum']['count'] = $day['retail']['count'] + $day['wholesale']['count'];

            // Doto Total (Retail + Wholesale + B2B Ship + B2B Aff + B2B Prom + Startup)
            // Note: Legacy includes specific logic, but broadly this is the Doto 'Internal' Sales
            $day['doto_total']['amount'] = $day['retail_wholesale_sum']['amount']
                                         + $day['b2b_shipping']['amount']
                                         + $day['b2b_affiliate']['amount']
                                         + $day['b2b_promo']['amount']
                                         + $day['startup']['amount'];
            $day['doto_total']['count'] = $day['retail_wholesale_sum']['count']
                                        + $day['b2b_shipping']['count']
                                        + $day['b2b_affiliate']['count']
                                        + $day['b2b_promo']['count']
                                        + $day['startup']['count'];

            // Marketing Total (Open + Rocket + General)
            $day['marketing_total']['amount'] = $day['open_market']['amount']
                                              + $day['rocket']['amount']
                                              + $day['general_mall']['amount'];
            $day['marketing_total']['count'] = $day['open_market']['count']
                                             + $day['rocket']['count']
                                             + $day['general_mall']['count'];

            // Grand Total (Doto + Linked + Marketing + Overseas)
            $day['grand_total']['amount'] = $day['doto_total']['amount'] 
                                          + $day['linked']['amount'] 
                                          + $day['marketing_total']['amount']
                                          + $day['overseas']['amount'];
            $day['grand_total']['count'] = $day['doto_total']['count'] 
                                         + $day['linked']['count'] 
                                         + $day['marketing_total']['count']
                                         + $day['overseas']['count'];
            
            // Ratios
            // Retail Ad Ratio: Retail Ad / Retail+Wholesale Sales
            if ($day['retail_wholesale_sum']['amount'] > 0) {
                $day['retail_ad_ratio']['amount'] = round(($day['retail_ad_cost']['amount'] / $day['retail_wholesale_sum']['amount']) * 100, 1);
            }

            // Marketing Ad Ratio: Marketing Ad / Marketing Sales
            if ($day['marketing_total']['amount'] > 0) {
                $day['marketing_ad_ratio']['amount'] = round(($day['marketing_ad_cost']['amount'] / $day['marketing_total']['amount']) * 100, 1);
            }
        }

        return $dates;
    }

    private function querySales($startDate, $endDate, $cfg, $type)
    {
        $query = DB::table('fm_order as a')
            ->join('fm_order_item as c', 'a.order_seq', '=', 'c.order_seq')
            ->leftJoin('fm_member as b', 'a.member_seq', '=', 'b.member_seq')
            ->leftJoin('fm_goods as d', 'c.goods_seq', '=', 'd.goods_seq')
            ->leftJoin('fm_member_emp as e', 'a.member_seq', '=', 'e.member_seq')
            ->select(
                DB::raw('DATE(a.deposit_date) as date'),
                DB::raw('SUM(a.settleprice + a.emoney) as total_amount'),
                DB::raw('COUNT(DISTINCT a.order_seq) as total_count')
            )
            ->whereBetween('a.deposit_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('a.mode', '!=', 'XMAS')
            ->whereBetween('a.step', ['25', '75'])
            ->groupBy(DB::raw('DATE(a.deposit_date)'));

        // Specific Logic by Type
        switch ($type) {
            case 'retail':
                $query->where(function($q) {
                    $q->where('b.gubun_seq', 0)
                      ->orWhereNull('b.gubun_seq')
                      ->orWhere('b.gubun_seq', '')
                      ->orWhere('b.mtype', '!=', 'business');
                });
                if (!empty($cfg['doto_id'])) $query->whereNotIn('b.userid', $cfg['doto_id']);
                break;

            case 'wholesale':
                $query->where('b.mtype', 'business')
                      ->whereNotIn('b.gubun_seq', ['2', '5'])
                      ->where('b.gubun_seq', '!=', 0)
                      ->whereNotNull('b.gubun_seq')
                      ->where('b.gubun_seq', '!=', '');
                if (!empty($cfg['doto_id'])) $query->whereNotIn('b.userid', $cfg['doto_id']);
                break;

            case 'b2b_shipping':
                $query->whereIn('b.gubun_seq', ['2', '5'])
                      ->where(function($q) {
                          $q->whereNull('e.emp_biz')->orWhere('e.emp_biz', '!=', 'Y');
                      });
                if (!empty($cfg['doto_id'])) $query->whereNotIn('b.userid', $cfg['doto_id']);
                if (!empty($cfg['affil_id'])) $query->whereNotIn('b.userid', $cfg['affil_id']);
                if (!empty($cfg['promo_id'])) $query->whereNotIn('b.userid', $cfg['promo_id']);
                break;

            case 'b2b_affiliate':
                $query->whereIn('b.gubun_seq', ['2', '5']);
                if (!empty($cfg['affil_id'])) $query->whereIn('b.userid', $cfg['affil_id']);
                else $query->whereRaw('1 = 0'); 
                break;

            case 'b2b_promo':
                $query->whereIn('b.gubun_seq', ['2', '5']);
                if (!empty($cfg['promo_id'])) $query->whereIn('b.userid', $cfg['promo_id']);
                else $query->whereRaw('1 = 0');
                break;

            case 'startup':
                $query->whereIn('b.gubun_seq', ['2', '5'])
                      ->where('e.emp_biz', 'Y');
                break;

            case 'linked':
                $query->where('a.site', 'SO')
                      ->whereIn('b.gubun_seq', ['2', '5']);
                break;
            
            case 'overseas':
                $query->where('d.goods_scode', 'like', 'GDR%');
                break;

            case 'open_market':
                $query->whereIn('a.member_seq', ['304080', '304081']);
                break;

            case 'rocket':
                $query->whereIn('a.member_seq', ['304651']);
                break;

            case 'general_mall':
                $query->whereIn('a.member_seq', ['304082', '304261', '305407']);
                break;
        }
        
        if (!empty($cfg['except_gd'])) {
            $query->whereNotIn('d.goods_seq', $cfg['except_gd']);
        }

        return $query->get();
    }

    private function getMarketingConfig()
    {
        // TODO: Recover these exact lists from legacy system
        // These are placeholders based on variable names
        return [
            'doto_id' => [], // Exclude form Retail/Wholesale
            'affil_id' => [], // 'b2bseller'
            'promo_id' => [], // 'b2bpromotion'
            'except_gd' => [], // Exclude Goods
        ];
    }
}
