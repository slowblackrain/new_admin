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
        /** @var \App\Models\Order $order */
        $order = Order::where('order_seq', $orderSeq)->firstOrFail();
        
        // Determine PG based on Goods
        $pgParams = $this->determinePg($order);
        
        if ($pgParams['pg'] == 'pairing') {
            return view('front.payment.request_pairing', compact('order', 'pgParams'));
        } elseif ($pgParams['pg'] == 'portone') {
            return view('front.payment.request_portone', compact('order', 'pgParams'));
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
        
        /** @var \App\Models\Order $order */
        $order = Order::where('order_seq', $orderId)->firstOrFail();
        
        // --- Security Check: Validate Client Amount Tampering ---
        if ((float)$amount !== (float)$order->settleprice) {
            \Illuminate\Support\Facades\Log::warning("Payment Amount Tampering Detected! Order: {$orderId}, Expected: {$order->settleprice}, Received: {$amount}");
            return redirect()->route('cart.index')->withErrors(['msg' => '결제 금액 정보가 일치하지 않습니다. 위변조가 의심되어 취소되었습니다.']);
        }
        
        // Verify Payment (Toss API)
        // Determine which secret key to use based on the order's items
        $pairingGoods = config('payment.pairing_goods', []);
        $usePairing = false;
        foreach ($order->items as $item) {
            if (in_array($item->goods_seq, $pairingGoods)) {
                $usePairing = true;
                break;
            }
        }
        
        $tossConfig = config('payment.toss');
        // If it was supposed to be pairing but somehow ended up here, or if we decide to use M mode for Toss instead of Cker
        if ($usePairing && isset($tossConfig['m_secret_key'])) {
             $secretKey = $tossConfig['is_test_mode'] ? $tossConfig['test_secret_key'] : $tossConfig['m_secret_key'];
        } else {
             $secretKey = $tossConfig['is_test_mode'] ? $tossConfig['test_secret_key'] : $tossConfig['r_secret_key'];
        }
        
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
            $order->pg_transaction_number = $data['paymentKey'] ?? $paymentKey;
            $order->pg_approval_number = $data['approvedAt'] ?? '0000';
            $order->save();
            
            // Delete Cart Items deferred from OrderController
            $cartSeqs = \Illuminate\Support\Facades\Cache::pull("order_cart_seqs_{$order->order_seq}");
            if ($cartSeqs && is_array($cartSeqs)) {
                \App\Models\Cart::whereIn('cart_seq', $cartSeqs)->delete();
                \App\Models\CartOption::whereIn('cart_seq', $cartSeqs)->delete();
                \App\Models\CartInput::whereIn('cart_seq', $cartSeqs)->delete();
            }
            
            // Insert PG Log? 
            // (fm_order_pg_log table structure needed, verify later)
            
            // --- Post-Payment Fulfillment Logic (Stock, Emoney, Logs) ---
            $this->finalizeOrderFulfillment($order, $data['paymentKey'] ?? $paymentKey, 'toss', '카드');

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

    public function tossWebhook(Request $request)
    {
        // Toss Payments Webhook payload
        $eventType = $request->input('eventType');
        $data = $request->input('data');
        
        if (!$data || !isset($data['orderId'])) {
            return response('Invalid Payload', 400);
        }

        $orderId = $data['orderId'];
        $status = $data['status']; // e.g., DONE, CANCELED, WAITING_FOR_DEPOSIT
        $paymentKey = $data['paymentKey'] ?? '';
        
        // We only care about deposit completion for virtual accounts
        if ($status !== 'DONE' && $status !== 'CANCELED') {
            return response('OK', 200); // Acknowledge receipt
        }

        Log::info("Toss Webhook Received: {$eventType} for Order: {$orderId}, Status: {$status}");

        /** @var \App\Models\Order|null $order */
        $order = Order::where('order_seq', $orderId)->first();
        if (!$order) {
            return response('Order Not Found', 404);
        }

        // Only process if the order is not fully paid yet
        if ($status === 'DONE' && $order->step < Order::STEP_PAYMENT_CONFIRMED) {
            $order->step = Order::STEP_PAYMENT_CONFIRMED;
            $order->deposit_yn = 'y';
            $order->pg_transaction_number = $paymentKey;
            $order->pg_approval_number = '0000';
            $order->save();

            // Run Fulfillment
            $this->finalizeOrderFulfillment($order, $paymentKey, 'toss', '가상계좌');
        } elseif ($status === 'CANCELED' && $order->step < 85) {
            $order->step = 85; // Cancelled
            $order->save();
        }

        return response('OK', 200);
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
                'clientId' => $config['is_test_mode'] ? $config['test_client_id'] : $config['client_id'],
                'apiUrl' => $config['api_url'],
                'goods_name' => $items->first()->goods_name,
            ];
        } else {
            $config = config('payment.portone');
            return [
                'pg' => 'portone',
                'storeId' => $config['store_id'],
                'channelKey' => $config['channel_key'],
                'channelKeyVbank' => $config['channel_key_vbank'],
                'channelKeyTransfer' => $config['channel_key_transfer'],
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
             /** @var \App\Models\Order|null $order */
             $order = Order::where('order_seq', $orderSeq)->first();
             if ($order) {
                 // Validate Amount
                 $paidAmount = $data['결제금액'] ?? $data['amount'] ?? 0;
                 if ((float)$paidAmount > 0 && (float)$paidAmount !== (float)$order->settleprice) {
                     \Illuminate\Support\Facades\Log::warning("Pairing Payment Amount Tampering Detected! Order: {$orderSeq}, Expected: {$order->settleprice}, Received: {$paidAmount}");
                     return response('Amount Mismatch', 400);
                 }

                 $order->step = Order::STEP_PAYMENT_CONFIRMED;
                 $order->deposit_yn = 'y';
                 $order->save();
                 
                 // Run Fulfillment
                 $this->finalizeOrderFulfillment($order, $data['거래번호'] ?? 'cker_tid', 'cker', '페어링');
             }
             return response('OK', 200);
        }
        
        return response('Fail', 400);
    }
    
    /**
     * Finalize Order Fulfillment (Stock Deduction, Emoney, B2B Logic)
     */
    private function finalizeOrderFulfillment(Order $order, $pgTid, $pgName, $methodName)
    {
        // 1. Emoney (Mileage) Deduction
        if ($order->emoney > 0 && $order->member_seq && $order->emoney_use === 'none') {
            DB::table('fm_member_emoney')->insert([
                'member_seq' => $order->member_seq,
                'type' => 'order',
                'emoney' => -$order->emoney,
                'memo' => "[차감]주문 ({$order->order_seq})에 의한 마일리지 차감",
                'regist_date' => now(),
            ]);
            $order->emoney_use = 'use';
            
            // Update member total
            DB::table('fm_member')->where('member_seq', $order->member_seq)
                ->update(['emoney' => DB::raw("emoney - {$order->emoney}")]);
        }

        // 2. Cash (예치금) Deduction
        if ($order->cash > 0 && $order->member_seq && $order->cash_use === 'none') {
            DB::table('fm_member_cash')->insert([
                'member_seq' => $order->member_seq,
                'type' => 'order',
                'cash' => -$order->cash,
                'memo' => "[차감]주문 ({$order->order_seq})에 의한 예치금 차감",
                'regist_date' => now(),
            ]);
            $order->cash_use = 'use';
            
            // Update member total
            DB::table('fm_member')->where('member_seq', $order->member_seq)
                ->update(['cash' => DB::raw("cash - {$order->cash}")]);
        }
        
        $order->save();

        // 3. Stock Deduction (fm_goods_supply and fm_goods)
        foreach ($order->items as $item) {
            $options = $item->options;
            if ($options) {
                foreach ($options as $opt) {
                    $ea = $opt->ea - $opt->refund_ea;
                    if ($ea > 0 && $item->goods_seq && $item->goods_seq != '9891' && $item->goods_seq != '67659') {
                        // Deduct from Supply
                        DB::table('fm_goods_supply')
                            ->where('goods_seq', $item->goods_seq)
                            ->whereNotNull('option_seq')
                            ->where('option_seq', '!=', '')
                            ->update([
                                'stock' => DB::raw("stock - {$ea}"),
                                'total_stock' => DB::raw("total_stock - {$ea}")
                            ]);
                            
                        // Deduct from Main Goods
                        DB::table('fm_goods')
                            ->where('goods_seq', $item->goods_seq)
                            ->update([
                                'tot_stock' => DB::raw("tot_stock - {$ea}")
                            ]);
                    }
                }
            }
        }

        // 4. Order Log
        DB::table('fm_order_log')->insert([
            'order_seq' => $order->order_seq,
            'actor' => 'pay',
            'title' => "결제확인",
            'detail' => "{$pgName} {$methodName} 결제 승인 확인 로그\n[TID: {$pgTid}]",
            'regist_date' => now()
        ]);
        // 5. ATS Auto-Copy Logic
        // Legacy Reference: ordermodel.php 1534: if($provider == "Y" && $step=='25' && $provider_YN == "Y")
        // Check if user is agency (provider = Y). For now we assume if member_seq > 0 and buys pairing goods, they are agency.
        $pairingGoods = config('payment.pairing_goods', []);
        $agencyCheck = DB::table('fm_member')->where('member_seq', $order->member_seq)->first();
        
        if ($agencyCheck && ($agencyCheck->provider ?? '') === 'Y') {
            $atsReplicationService = app(\App\Services\AtsReplicationService::class);
            $atsReplicated = false;

            foreach ($order->items as $item) {
                if (in_array($item->goods_seq, $pairingGoods)) {
                    $atsReplicationService->replicateAtsToAgency($item->goods_seq, $order->member_seq);
                    $atsReplicated = true;
                }
            }
            
            // Legacy 1554: if agency bought ATS, force step 55 (배송중) directly since it's a virtual good sync
            if ($atsReplicated) {
                $order->step = \App\Models\Order::STEP_SHIPPED ?? 55;
                $order->save();
            }
        }
    }

    /**
     * 포트원 결제 성공 콜백 수신
     */
    public function portoneSuccess(Request $request)
    {
        $paymentId = $request->input('paymentId');
        if (empty($paymentId)) {
            return redirect()->route('cart.index')->withErrors(['msg' => '결제 정보(paymentId)가 존재하지 않습니다.']);
        }

        // 1. 포트원 V2 단건결제조회 API 호출
        $portoneData = $this->callPortoneApi($paymentId);
        if (!$portoneData) {
            return redirect()->route('cart.index')->withErrors(['msg' => '포트원 결제 정보를 검증하는 중 통신 오류가 발생했습니다.']);
        }

        $status = $portoneData['status'] ?? '';
        $paidAmount = (int) ($portoneData['amount']['total'] ?? 0);
        $transactionId = $portoneData['transactionId'] ?? '';

        // PAID (결제완료) 또는 VIRTUAL_ACCOUNT_ISSUED (가상계좌발급) 상태 검증
        if (!in_array($status, ['PAID', 'VIRTUAL_ACCOUNT_ISSUED'])) {
            return redirect()->route('cart.index')->withErrors(['msg' => '올바른 결제 완료 상태가 아닙니다. (' . $status . ')']);
        }

        // 2. 내부 데이터베이스와 결제 금액 비교 검증
        /** @var \App\Models\Order $order */
        $order = Order::where('order_seq', $paymentId)->firstOrFail();
        $dbAmount = (int) $order->settleprice;

        if ($paidAmount !== $dbAmount) {
            Log::warning("PortOne Payment Amount Tampering Detected! Order: {$paymentId}, Expected: {$dbAmount}, Received: {$paidAmount}");
            return redirect()->route('cart.index')->withErrors(['msg' => '결제된 금액과 내부 주문 금액이 일치하지 않습니다. 위변조가 의심됩니다.']);
        }

        // 3. 검증 완료 후 처리
        DB::beginTransaction();
        try {
            if ($status === 'PAID') {
                if ($order->step < Order::STEP_PAYMENT_CONFIRMED) {
                    $order->step = Order::STEP_PAYMENT_CONFIRMED;
                    $order->pg = 'portone';
                    $order->deposit_yn = 'y';
                    $order->pg_transaction_number = $transactionId;
                    $order->pg_approval_number = $portoneData['approvedAt'] ?? '0000';
                    $order->save();

                    // Delete Cart Items
                    $cartSeqs = \Illuminate\Support\Facades\Cache::pull("order_cart_seqs_{$order->order_seq}");
                    if ($cartSeqs && is_array($cartSeqs)) {
                        \App\Models\Cart::whereIn('cart_seq', $cartSeqs)->delete();
                        \App\Models\CartOption::whereIn('cart_seq', $cartSeqs)->delete();
                        \App\Models\CartInput::whereIn('cart_seq', $cartSeqs)->delete();
                    }

                    $this->finalizeOrderFulfillment($order, $transactionId, 'portone', '신용카드');
                }
            } elseif ($status === 'VIRTUAL_ACCOUNT_ISSUED') {
                if ($order->step < 15) {
                    $virtualAccountStr = '';
                    $bankDepositor = $portoneData['customer']['name'] ?? '';
                    $virtualDate = null;

                    if (isset($portoneData['method']) && $portoneData['method']['type'] === 'PaymentMethodVirtualAccount') {
                        $portoneBankMap = [
                            'NONGHYUP'=>'농협','KOOKMIN'=>'국민','SHINHAN'=>'신한','WOORI'=>'우리','HANA'=>'하나',
                            'KAKAOBANK'=>'카카오뱅크','TOSS'=>'토스뱅크','K_BANK'=>'케이뱅크','IBK'=>'기업','SC'=>'SC제일',
                            'CITY'=>'한국씨티','SUHYUP'=>'수협','POST'=>'우체국','BUSAN'=>'부산','KYONGNAM'=>'경남',
                            'DAEGU'=>'대구','KWANGJU'=>'광주','JEONBUK'=>'전북','JEJU'=>'제주','CU'=>'신협',
                            'MG'=>'새마을금고','SBI'=>'SBI저축','KFCC'=>'새마을금고','KB'=>'국민'
                        ];
                        $acc = $portoneData['method'];
                        $bankName = isset($acc['bank']) ? ($portoneBankMap[$acc['bank']] ?? $acc['bank']) : '';
                        $virtualAccountStr = trim($bankName . ' ' . ($acc['accountNumber'] ?? ''));
                        if (isset($acc['expiredAt'])) {
                            $virtualDate = date('Y-m-d H:i:s', strtotime($acc['expiredAt']));
                        }
                    }

                    $order->step = 15; // 미입금 (주문접수)
                    $order->payment = 'virtual';
                    $order->pg = 'portone';
                    $order->virtual_account = $virtualAccountStr;
                    $order->depositor = $bankDepositor;
                    if ($virtualDate) {
                        $order->virtual_date = $virtualDate;
                    }
                    $order->pg_transaction_number = $transactionId;
                    $order->save();

                    // Delete Cart Items
                    $cartSeqs = \Illuminate\Support\Facades\Cache::pull("order_cart_seqs_{$order->order_seq}");
                    if ($cartSeqs && is_array($cartSeqs)) {
                        \App\Models\Cart::whereIn('cart_seq', $cartSeqs)->delete();
                        \App\Models\CartOption::whereIn('cart_seq', $cartSeqs)->delete();
                        \App\Models\CartInput::whereIn('cart_seq', $cartSeqs)->delete();
                    }

                    $this->finalizeOrderFulfillment($order, $transactionId, 'portone', '가상계좌 발급');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PortOne post-payment database process failed: " . $e->getMessage());
            return redirect()->route('cart.index')->withErrors(['msg' => '결제 승인 처리 중 내부 데이터베이스 오류가 발생했습니다.']);
        }

        return redirect()->route('order.complete', ['id' => $order->order_seq]);
    }

    /**
     * 포트원 결제 실패 콜백 수신
     */
    public function portoneFail(Request $request)
    {
        $msg = $request->input('message', '결제가 취소되었거나 실패했습니다.');
        $code = $request->input('code', '');
        return redirect()->route('cart.index')->withErrors(['msg' => "결제 실패: [{$code}] {$msg}"]);
    }

    /**
     * 포트원 결제 웹훅 수신
     */
    public function portoneWebhook(Request $request)
    {
        $payload = $request->all();
        $paymentId = $payload['data']['paymentId'] ?? null;

        if (!$paymentId) {
            return response('Payment ID missing', 400);
        }

        // 포트원 V2 단건조회 API를 통해 실제 데이터 검증
        $portoneData = $this->callPortoneApi($paymentId);
        if (!$portoneData) {
            return response('Verification Failed', 400);
        }

        $status = $portoneData['status'] ?? '';
        $transactionId = $portoneData['transactionId'] ?? '';

        /** @var \App\Models\Order|null $order */
        $order = Order::where('order_seq', $paymentId)->first();
        if (!$order) {
            return response('Order Not Found', 404);
        }

        DB::beginTransaction();
        try {
            if ($status === 'PAID') {
                if ($order->step < Order::STEP_PAYMENT_CONFIRMED) {
                    $order->step = Order::STEP_PAYMENT_CONFIRMED;
                    $order->pg = 'portone';
                    $order->deposit_yn = 'y';
                    $order->deposit_date = now();
                    $order->pg_transaction_number = $transactionId;
                    $order->save();

                    $this->finalizeOrderFulfillment($order, $transactionId, 'portone', '가상계좌 입금(웹훅)');
                }
            } elseif ($status === 'VIRTUAL_ACCOUNT_ISSUED') {
                if ($order->step < 15) {
                    $virtualAccountStr = '';
                    $bankDepositor = $portoneData['customer']['name'] ?? '';
                    $virtualDate = null;

                    if (isset($portoneData['method']) && $portoneData['method']['type'] === 'PaymentMethodVirtualAccount') {
                        $portoneBankMap = [
                            'NONGHYUP'=>'농협','KOOKMIN'=>'국민','SHINHAN'=>'신한','WOORI'=>'우리','HANA'=>'하나',
                            'KAKAOBANK'=>'카카오뱅크','TOSS'=>'토스뱅크','K_BANK'=>'케이뱅크','IBK'=>'기업','SC'=>'SC제일',
                            'CITY'=>'한국씨티','SUHYUP'=>'수협','POST'=>'우체국','BUSAN'=>'부산','KYONGNAM'=>'경남',
                            'DAEGU'=>'대구','KWANGJU'=>'광주','JEONBUK'=>'전북','JEJU'=>'제주','CU'=>'신협',
                            'MG'=>'새마을금고','SBI'=>'SBI저축','KFCC'=>'새마을금고','KB'=>'국민'
                        ];
                        $acc = $portoneData['method'];
                        $bankName = isset($acc['bank']) ? ($portoneBankMap[$acc['bank']] ?? $acc['bank']) : '';
                        $virtualAccountStr = trim($bankName . ' ' . ($acc['accountNumber'] ?? ''));
                        if (isset($acc['expiredAt'])) {
                            $virtualDate = date('Y-m-d H:i:s', strtotime($acc['expiredAt']));
                        }
                    }

                    $order->step = 15;
                    $order->payment = 'virtual';
                    $order->pg = 'portone';
                    $order->virtual_account = $virtualAccountStr;
                    $order->depositor = $bankDepositor;
                    if ($virtualDate) {
                        $order->virtual_date = $virtualDate;
                    }
                    $order->pg_transaction_number = $transactionId;
                    $order->save();

                    $this->finalizeOrderFulfillment($order, $transactionId, 'portone', '가상계좌 발급(웹훅)');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PortOne Webhook processing error: " . $e->getMessage());
            return response('Error', 500);
        }

        return response('OK', 200);
    }

    /**
     * 포트원 V2 단건 결제조회 API 호출 헬퍼
     */
    private function callPortoneApi($paymentId)
    {
        $apiSecret = config('payment.portone.api_secret');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.portone.io/payments/' . urlencode($paymentId),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: PortOne ' . $apiSecret,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }
}
