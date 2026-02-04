<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function request(Request $request)
    {
        $orderSeq = $request->input('order_seq');
        $order = Order::where('order_seq', $orderSeq)->firstOrFail();
        
        // Determine PG based on Goods
        $pgParams = $this->determinePg($order);
        
        if ($pgParams['pg'] == 'pairing') {
            return view('front.payment.request_pairing', compact('order', 'pgParams'));
        } else {
            return view('front.payment.request_toss', compact('order', 'pgParams'));
        }
    }

    public function success(Request $request)
    {
        // Toss Success
        // Params: paymentKey, orderId, amount
        $paymentKey = $request->input('paymentKey');
        $orderId = $request->input('orderId');
        $amount = $request->input('amount');
        
        if (!$orderId) {
             // Pairing Success might come differently or via callback (Check implementation)
             // Pairing legacy: succ method handles success. 
             // Pairing redirects to success url?
        }
        
        $order = Order::where('order_seq', $orderId)->firstOrFail();
        
        // Verify Payment (Toss API)
        $tossConfig = config('payment.toss');
        $secretKey = $tossConfig['secret_key'];
        
        $credential = base64_encode($secretKey . ':');
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.tosspayments.com/v1/payments/confirm',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'paymentKey' => $paymentKey,
                'orderId' => $orderId,
                'amount' => $amount,
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $credential,
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            
            // Payment Success
            $order->step = Order::STEP_PAYMENT_CONFIRMED; // 25
            $order->deposit_yn = 'y';
            $order->pg_tid = $data['paymentKey'] ?? $paymentKey;
            $order->pg_result_code = '0000';
            $order->save();
            
            // Insert PG Log? 
            // (fm_order_pg_log table structure needed, verify later)
            
            return redirect()->route('order.complete', ['id' => $order->order_seq]);
        } else {
            $data = json_decode($response, true);
            $msg = $data['message'] ?? '결제 승인 중 오류가 발생했습니다.';
            return redirect()->route('order.settle', ['mode' => 'order'])->withErrors(['msg' => $msg]);
        }
    }
    
    public function fail(Request $request)
    {
        $msg = $request->input('message', '결제가 취소되었거나 실패했습니다.');
        $code = $request->input('code', '');
        
        return redirect()->route('cart.index')->withErrors(['msg' => "결제 실패: [{$code}] {$msg}"]);
    }

    private function determinePg($order)
    {
        $items = $order->items;
        $pairingGoods = config('payment.pairing_goods', []);
        
        $usePairing = false;
        
        foreach ($items as $item) {
            if (in_array($item->goods_seq, $pairingGoods)) {
                $usePairing = true;
                break;
            }
        }
        
        if ($usePairing) {
            $config = config('payment.pairing');
            return [
                'pg' => 'pairing',
                'clientId' => $config['client_id'],
                'apiUrl' => $config['api_url'],
                'goods_name' => $items->first()->goods_name, // e.g., "Point Charge"
            ];
        } else {
            $config = config('payment.toss');
            return [
                'pg' => 'toss',
                'clientKey' => $config['client_key'],
                'customerName' => $order->order_user_name,
                'goods_name' => $items->first()->goods_name . (count($items) > 1 ? ' 외 ' . (count($items) - 1) . '건' : ''),
            ];
        }
    }
    
    // Pairing Callback (Receive)
    public function pairingReceive(Request $request) 
    {
        // Handle Pairing server-to-server callback or form post?
        // Legacy pairing.php receive() uses $_POST['data'] JSON.
        // It seems pairing sends a POST notification.
        
        $data = $request->input('data');
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        if (!$data) {
             return response('Invalid Data', 400);
        }
        
        $orderSeq = $data['주문번호'] ?? null; // Based on legacy: $event['data']['주문번호'] or $event['주문번호']?
        // Legacy receive(): 
        // if($_GET['type'] == 'p') $event = json_decode($_POST['data'], true);
        // $res_cd = $event['결과코드'];
        
        // I need to be careful about matching legacy exact logic references.
        // Legacy lines 455: $res_cd = $event['결과코드'];
        // Legacy lines 473: $ordr_idxx = $_GET['orderno']; (It seems it gets orderno from GET param in callback URL)
        
        $orderSeq = $request->input('orderno'); 
        
        if ($data['결과코드'] == '0000' || $data['code'] == '0000') {
            // Success
             $order = Order::where('order_seq', $orderSeq)->first();
             if ($order) {
                 $order->step = Order::STEP_PAYMENT_CONFIRMED;
                 $order->deposit_yn = 'y';
                 $order->save();
             }
             return response('OK', 200);
        }
        
        return response('Fail', 400);
    }
}
