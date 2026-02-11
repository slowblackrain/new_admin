<?php

namespace App\Services\Order;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Models\Goods;

class OrderExcelService
{
    protected $itemList = array(
        "order_name"                            => "구매자",
        "order_phone"                           => "구매자전화",
        "order_cell"                            => "구매자핸드폰",
        "order_mail"                            => "구매자메일",
        "recive_name"                           => "수령인",
        "recive_phone"                          => "수령인전화",
        "recive_mobile"                         => "수령인핸드폰",
        "zipcode"                               => "우편번호",
        "addr"                                  => "주소",
        "delivery"                              => "배송비",
        "goods_name"                            => "상품명",
        "goods_ea"                              => "수량",
        "goods_prt"                             => "인쇄",
        "blank"                                 => "세금계산서",
        "memo"                                  => "메모",
        "clearance_unique_personal_code"        => "개인통관고유부호",
        "goods_code"                            => "상품코드",
        "box_cbm"                               => "박스CBM", // Legacy comment in code suggests this
        "multi_discount_cbm"                    => "다중CBM",
    );

    protected $providerInfo;

    public function __construct()
    {
        // Provider info is usually fetched from Auth
        $seller = Auth::guard('seller')->user();
        if ($seller) {
            $this->providerInfo = [
                'provider_seq' => $seller->provider_seq,
                'provider_id' => $seller->provider_id,
            ];
        }
    }

    public function excel_upload($file_path, $mode = 'check')
    {
        $result_data = [];
        $result_error = [];
        $dataCount = 0;
        $orderCount = 0;
        $orderData = [];
        $excel_data = [];

        // 1. Read CSV
        $handle = fopen($file_path, "r");
        if ($handle === FALSE) {
            return [['error' => 'File open failed'], []];
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
             // Convert encoding if necessary
             // Assuming input might be EUC-KR (common in KR Excel), convert to UTF-8
             $excel_data[] = array_map(function($str){
                 return mb_convert_encoding($str, "UTF-8", "EUC-KR, UTF-8");
             }, $data);
        }
        fclose($handle);

        if (empty($excel_data)) {
             return [['error' => 'Empty file'], []];
        }

        // 2. Process Header
        $header = $excel_data[0];
        
        $col_map = [];
        foreach($this->itemList as $key => $val){
             $found = array_search($val, $header);
             if($found !== false){
                 $col_map[$key] = $found;
             }
        }

        // 3. Process Rows
        $member = null;
        $current_emoney = 0;
        $total_order_price = 0;

        if($this->providerInfo){
             $member = DB::table('fm_member')->where('userid', $this->providerInfo['provider_id'])->first();
             $member = (array)$member;
             $current_emoney = $member['emoney'];
        }

        foreach ($excel_data as $k => $row) {
            if ($k == 0) continue; 
            
            // Map row data to keys
            $mapped_row = [];
            foreach($col_map as $key => $idx){
                $mapped_row[$key] = $row[$idx] ?? '';
            }

            // Call Filter
            $res = $this->excel_filter_order($member, $orderData, $orderCount, $mapped_row, $dataCount, $k);
            
            if(isset($res['error_msg']) && $res['error_msg']){
                $mapped_row['error'] = $res['error_msg'];
                $mapped_row['line'] = $k + 1;
                $result_error[] = $mapped_row;
            } else {
                 if($res){
                     // Accumulate Price for Emoney Check
                     // $res is the specific row data (or array of rows if split)
                     // Simple case: $res is the modified row.
                     $item_price = $res['settleprice'] ?? 0;
                     $total_order_price += $item_price;
                     
                     $res['now_emoney'] = $current_emoney - $total_order_price;
                     if($res['now_emoney'] < 0){
                         $res['error'] = '적립금 부족';
                         $result_error[] = $res;
                     } else {
                         $orderData[] = $res;
                     }
                 }
            }
            $dataCount++;
            $orderCount++; 
        }

        return [$orderData, $result_error];
    }

    public function create_orders($orders, $seller)
    {
        DB::beginTransaction();

        try {
            // Get Member Info
            $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();
            if (!$member) {
                throw new \Exception("Member not found for provider: " . $seller->provider_id);
            }
            
            $total_amount = 0;
            $created_count = 0;
            $current_emoney = $member->emoney;

            foreach ($orders as $row) {
                // 1. Calculate and Check Balance
                $settleprice = (int)$row['settleprice'];
                if ($current_emoney < $settleprice) {
                    throw new \Exception("적립금이 부족합니다. (부족금액: " . number_format($settleprice - $current_emoney) . ")");
                }

                // 2. Insert Order
                $order_seq = DB::table('fm_order')->insertGetId([
                    'regist_date' => now(),
                    'step' => 15, // Payment Confirmed / Deposited
                    'order_type' => 'personal', // Or 'admin' or 'agency'
                    'payment' => 'point', // Use Point (Emoney)
                    'settleprice' => $settleprice,
                    'price' => (int)$row['price'],
                    'delivery_price' => (int)$row['delivery_price'],
                    'emoney' => $settleprice, // Paid by emoney
                    'cash' => 0,
                    'order_user_name' => $row['order_name'] ?? $member->user_name,
                    'order_phone' => $row['order_phone'] ?? $member->phone,
                    'order_cell' => $row['order_cell'] ?? $member->cellphone,
                    'order_email' => $row['order_email'] ?? $member->email,
                    'recipient_user_name' => $row['recive_name'],
                    'recipient_phone' => $row['recive_phone'],
                    'recipient_cell' => $row['recive_cell'] ?? $row['recive_mobile'] ?? '', 
                    'recipient_zipcode' => $row['zipcode'],
                    'recipient_address' => $row['addr'],
                    'recipient_address_street' => $row['addr'], 
                    'memo' => $row['memo'] ?? '',
                    'member_seq' => $member->member_seq,
                    'order_ip' => request()->ip(),
                    'sgl' => 0, 
                ]);

                // 3. Insert Order Item
                DB::table('fm_order_item')->insert([
                    'order_seq' => $order_seq,
                    'goods_seq' => $row['goods_seq'],
                    'goods_name' => $row['goods_name'],
                    'goods_code' => $row['goods_code'],
                    'ea' => $row['goods_ea'],
                    'price' => (int)$row['price'],
                    'supply_price' => 0, 
                    'consumer_price' => 0,
                    'step' => 15,
                    'regist_date' => now(),
                    'provider_seq' => $this->providerInfo['provider_seq'], 
                ]);
                
                // 4. Insert Shipping
                 DB::table('fm_order_shipping')->insert([
                    'order_seq' => $order_seq,
                    'shipping_method' => 'delivery', 
                    'shipping_cost' => (int)$row['delivery_price'],
                    'provider_seq' => 1, 
                 ]);

                // 5. Deduct Emoney
                DB::table('fm_member')->where('member_seq', $member->member_seq)->decrement('emoney', $settleprice);
                
                // 6. Log Emoney
                DB::table('fm_emoney')->insert([
                    'member_seq' => $member->member_seq,
                    'gb' => 'minus',
                    'emoney' => -$settleprice,
                    'remain' => $current_emoney - $settleprice,
                    'memo' => '엑셀주문차감 (주문번호: ' . $order_seq . ')',
                    'regist_date' => now(),
                    'order_seq' => $order_seq,
                ]);

                $current_emoney -= $settleprice;
                $total_amount += $settleprice;
                $created_count++;
            }

            DB::commit();
            return ['success' => true, 'count' => $created_count, 'message' => "{$created_count}건의 주문이 정상적으로 접수되었습니다."];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => '주문 처리 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    public function excel_filter_order($member, $orderData, $orderCount, $row, $dataCount, $lineNum)
    {
        // 1. Check Goods Code
        $goods_code = $row['goods_code'] ?? '';
        if(!$goods_code) return ['error_msg' => '상품코드 누락'];

        $goods = Goods::where('goods_code', $goods_code)->first();
        if(!$goods) return ['error_msg' => '존재하지 않는 상품코드 (' . $goods_code . ')'];

        // 3. Calculate Price
        $ea = (int)($row['goods_ea'] ?? 1);
        if($ea < 1) $ea = 1;

        $price = $goods->price * $ea; 
        
        $row['goods_seq'] = $goods->goods_seq;
        $row['goods_name'] = $goods->goods_name;
        $row['price'] = $goods->price;
        $row['goods_ea'] = $ea;
        $row['option_sno'] = ''; 

        // Delivery
        $row['delivery_price'] = 3000; 
        $row['settleprice'] = $price + $row['delivery_price'];

        return $row;
    }
}
