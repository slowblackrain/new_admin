<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LegacyGoodsHelper
{
    public static function getOfferInfoHtml($goodsSeq)
    {
        $html = "";
        
        // Query matching legacy: (step < 11 || step = 13 || step = 14 || step = 100) and step > 0
        $offers = DB::table('fm_offer')
            ->where('goods_seq', $goodsSeq)
            ->where(function($q) {
                $q->where('step', '<', 11)
                  ->orWhere('step', 13)
                  ->orWhere('step', 14)
                  ->orWhere('step', 100);
            })
            ->where('step', '>', 0)
            ->orderBy('sno', 'desc')
            ->get();

        if ($offers->isEmpty()) {
            return "";
        }

        $rows = "";
        $count = 0;
        $daily = ['일','월','화','수','목','금','토'];

        foreach ($offers as $data) {
            $data = (array)$data; // Convert stdClass to array
            
            // Legacy Logic for Step Status & Color
            $step_status = "";
            $bgcolor = "";
            $ord_date = substr($data['order_date'], 0, 10);

            // Step translation
            switch($data['step']){
                case "6"  : $step_status = "발대"; $bgcolor = "#669900"; $ord_date = substr($data['order_date'], 0, 10); break;
                case "1"  : $step_status = "주문"; $bgcolor = "#FFC4F1"; $ord_date = substr($data['order_date'], 0, 10); break;
                case "2"  : $step_status = "가입고"; $bgcolor = "#66cc00"; $ord_date = substr($data['order_date'], 0, 10); break;
                case "3"  : $step_status = "재조사"; $bgcolor = "#66cc00"; $ord_date = substr($data['order_date'], 0, 10); break;
                case "4"  : 
                    $today = date('Y-m-d');
                    $step_status = "중입예"; $bgcolor = "#FF9933";
                    if($today > $data['ordering_date']){
                        $ord_date = $today;
                    }else{
                        $ord_date = substr($data['ordering_date'], 0, 10);
                    }
                    break;
                case "5"  : $step_status = "중입-"; $bgcolor = "#58C2FC"; $ord_date = substr($data['cn_date'], 0, 10); break;
                case "7"  : $step_status = "중입+"; $bgcolor = "#58C2FC"; $ord_date = substr($data['cn_date'], 0, 10); break;
                case "8"  : $step_status = "선적"; $bgcolor = "#AAFFAE"; $ord_date = substr($data['shipment_date'], 0, 10); break;
                case "9"  : $step_status = "선대기"; $bgcolor = "#35a160"; $ord_date = substr($data['shipment_date'], 0, 10); break;
                case "10" : $step_status = "한입예"; $bgcolor = "#ffe28c"; $ord_date = substr($data['shipment_date'], 0, 10); break;
                case "11" : $step_status = "한입고"; $bgcolor = "#EEEEEE"; $ord_date = substr($data['stock_date'], 0, 10); break;
                case "12" : $step_status = "한반품"; $bgcolor = "#CCCCCC"; break;
                case "13" : $step_status = "중반품"; $bgcolor = "#CCCCCC"; $ord_date = substr($data['cn_date'], 0, 10); break;
                case "14" : $step_status = "중재고"; $bgcolor = "#ccccff"; $ord_date = substr($data['cn_date'], 0, 10); break;
                case "100": $step_status = "대행";   $bgcolor = "#cc9999"; $ord_date = substr($data['order_date'], 0, 10); break;
            }

            // Admin Name Logic
            $ord_admin = explode("|", $data['ord_charge'])[0];
            $tmp_word = "";
            
            if(strpos($ord_admin,"dometopia") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='대표님'>★</span>";
            else if(strpos($ord_admin,"newjjang3") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='장승호'>◆</span>";
            else if(strpos($ord_admin,"icn0222") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='인치남'>■</span>";
            else if(strpos($ord_admin,"jysjoy") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='장영숙'>●</span>";
            else if(strpos($ord_admin,"toto1991792") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='제유진'>♣</span>";
            else if(strpos($ord_admin,"ami3000") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='조성규'>♥</span>";
            else if(strpos($ord_admin,"chlwnsgmlsla") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='최준희'>♠</span>";
            else if(strpos($ord_admin,"ms1201") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='이미숙'>▲</span>";
            else if(strpos($ord_admin,"topia8") !== false) $tmp_word = "<span style='color:#FF0000;cursor:pointer' title='김택용'>◎</span>";

            // Count duplicates logic (simplified)
            if($data['offer_cn']){
                $dupCnt = DB::table('fm_offer')->where('step', 8)->where('shipment_date', $data['shipment_date'])->where('offer_cn', $data['offer_cn'])->count();
                if($dupCnt > 1) {
                    $tmp_word .= "<span class='helpicon' title='".$data['offer_cn']."'>"; // Missing close tag in legacy?
                }
            }

            if($data['visitant'] == '인쇄') {
                $step_status = "P " . $step_status;
            }
            if($data['visitant'] && $data['visitant'] != '자판' && $data['visitant'] != '인쇄') {
                $step_status = "(".$data['visitant'].") ".$step_status;
            }
            if(strpos($data['offer_cn'] ?? '', "F") !== false) {
                $step_status = "F " . $step_status;
            }

            // Ord Stock Logic
            $ord_total_parts = array_filter(explode("|", $data['ord_total']));
            $data['ord_stock'] = array_pop($ord_total_parts);

            // Date Yoil
            $yoil = date("w", strtotime($ord_date));
            $yoil_txt = isset($daily[$yoil]) ? $daily[$yoil] : '';

            $count++;
            $searchLink = "/admin/scm_doto_warehousing/catalog?sch_step={$data['step']}&goods_seq={$data['goods_seq']}";
            
            $rows .= "<tr>
                <td bgcolor='{$bgcolor}' style='font-size:11px; padding:2px;'>{$count}</td>
                <td bgcolor='{$bgcolor}' style='padding-left: 5px; font-size:11px;'>
                    <a href='{$searchLink}' target='_blank' title='".($data['scm_memo'] ?? '')."'>
                        <font color='black'>{$ord_date}({$yoil_txt}) {$step_status} ".number_format((float)$data['ord_stock'])."</font>
                        {$tmp_word}
                    </a>
                </td>
            </tr>";
        }

        return "<table border=0 align='center' class='goodsofferview' width='100%' cellpadding=0 cellspacing=0 style='margin:0;'>{$rows}</table>";
    }

    public static function getDiscountPriceHtml($goodsSeq)
    {
        $v = DB::table('fm_goods as a')
            ->leftJoin('fm_goods_option as b', 'a.goods_seq', '=', 'b.goods_seq')
            ->where('a.goods_seq', $goodsSeq)
            ->where('b.default_option', 'y') // Ensure we get default option price
            ->select('a.mtype_discount', 'a.fifty_discount', 'a.hundred_discount', 'b.price')
            ->first();

        if (!$v) return "";
        $v = (array)$v;

        if (!$v['mtype_discount']) {
            return "<div style='padding:5px;'>".number_format($v['price'])."</div>";
        }

        $html = "<div style='width:100%;padding-right:5px; font-size:11px;'>";
        
        // Calculate discounted prices (Legacy logic subtracts discount amount from price)
        $mtype_discounted = $v['price'] - $v['mtype_discount'];
        
        // Check for admin session (assumed true for admin context in Laravel)
        // Legacy: if($this->session->userdata["manager"]["manager_id"]) ...
        // We will output it if valuable.
        
        if ($v['hundred_discount']) {
            $hundred_val = $v['price'] - $v['hundred_discount'];
            $html .= "<div style='padding:2px;'><span style='display:inline-block;width:25px;color:#FF5555'>수▲</span><span style='display:inline-block;text-align:right;width:55px;color:#5555FF'>".number_format($hundred_val)."</span></div>";
        }
        if ($v['fifty_discount']) {
            $fifty_val = $v['price'] - $v['fifty_discount'];
            $html .= "<div style='padding:2px;'><span style='display:inline-block;width:25px;color:#FF5555'>할▲</span><span style='display:inline-block;text-align:right;width:55px;color:#5555FF'>".number_format($fifty_val)."</span></div><div style='height:3px;'></div>";
        }

        $html .= "<div style='padding:2px;'><span style='display:inline-block;width:25px;color:#FF5555'>도▲</span><span style='display:inline-block;text-align:right;width:55px;color:#5555FF'>".number_format($mtype_discounted)."</span></div>";
        $html .= "<div style='padding:2px;'><span style='display:inline-block;width:25px;color:#FF5555'>소▲</span><span style='display:inline-block;text-align:right;width:55px;color:#5555FF'>".number_format($v['price'])."</span></div>";
        
        $html .= "</div>";
        return $html;
    }
}
