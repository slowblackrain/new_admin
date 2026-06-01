<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrderReturn;
use App\Models\OrderRefund;
use App\Models\Order;

class ClaimController extends Controller
{
    /**
     * 클레임 통합 리스트
     */
    public function index(Request $request)
    {
        // 탭 구분: all(전체), returns(반품/교환), refund(환불/결제취소)
        $tab = $request->input('tab', 'all');
        $keyword = $request->input('keyword', '');
        $status = $request->input('status', []); // array of statuses e.g. ['request', 'ing', 'complete']

        $perPage = 20;

        $returns = collect();
        $refunds = collect();

        // 1. 반품/교환 데이터 (fm_order_return)
        if (in_array($tab, ['all', 'returns'])) {
            $query = OrderReturn::with('order')->orderBy('regist_date', 'desc');

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('return_code', 'like', "%{$keyword}%")
                      ->orWhere('order_seq', 'like', "%{$keyword}%");
                });
            }
            if (!empty($status)) {
                $query->whereIn('status', $status);
            }

            $returns = $query->paginate($perPage, ['*'], 'return_page')->appends(request()->query());
        }

        // 2. 환불/결제취소 데이터 (fm_order_refund)
        if (in_array($tab, ['all', 'refund'])) {
            $query = OrderRefund::with('order')->orderBy('regist_date', 'desc');

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('refund_code', 'like', "%{$keyword}%")
                      ->orWhere('order_seq', 'like', "%{$keyword}%");
                });
            }
            if (!empty($status)) {
                $query->whereIn('status', $status);
            }

            $refunds = $query->paginate($perPage, ['*'], 'refund_page')->appends(request()->query());
        }

        return view('admin.order.claim_list', compact('tab', 'returns', 'refunds', 'keyword', 'status'));
    }

    /**
     * 선택된 클레임 내역 일괄 상태 처리 (AJAX)
     */
    public function process(Request $request)
    {
        $type = $request->input('type'); // 'return' or 'refund'
        $codes = $request->input('codes', []);
        $newStatus = $request->input('status'); // 'request', 'ing', 'complete'

        if (empty($codes) || !$newStatus || !in_array($type, ['return', 'refund'])) {
            return response()->json(['success' => false, 'message' => '잘못된 요청입니다.']);
        }

        try {
            DB::beginTransaction();

            if ($type == 'return') {
                OrderReturn::whereIn('return_code', $codes)->update([
                    'status' => $newStatus,
                    'status_date' => now()
                ]);
            } else {
                // refund
                if ($newStatus === 'complete') {
                    $refunds = OrderRefund::whereIn('refund_code', $codes)->get();
                    foreach ($refunds as $rf) {
                        $order = $rf->order;
                        if (!$order) continue;

                        // 주문의 PG가 portone인 경우 실제로 포트원 API 취소를 먼저 실행
                        if ($order->pg === 'portone' && $rf->refund_price > 0) {
                            // 가상계좌(payment == 'virtual') 주문 환불 시 은행 정보 유효성 강제 검사
                            if ($order->payment === 'virtual') {
                                if (empty($rf->bank_name) || empty($rf->bank_account) || empty($rf->bank_depositor)) {
                                    throw new \Exception("가상계좌 결제 환불 건 [환불코드: {$rf->refund_code}]의 환불 은행명, 계좌번호, 예금주명 정보가 누락되었습니다. 계좌 정보를 입력 후 처리해주세요.");
                                }
                            }

                            // 포트원 V2 취소 API 호출
                            $cancelResult = $this->cancelPortonePayment($order, $rf);
                            if (!$cancelResult['success']) {
                                throw new \Exception("[환불코드: {$rf->refund_code}] 포트원 결제 취소 API 승인 실패: " . $cancelResult['message']);
                            }
                        }
                    }
                }

                $updateData = ['status' => $newStatus];
                if ($newStatus === 'complete') {
                    $updateData['refund_date'] = now();
                }

                OrderRefund::whereIn('refund_code', $codes)->update($updateData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => '상태 값이 성공적으로 변경되었습니다.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 포트원 V2 결제 취소 API 연동 헬퍼
     */
    private function cancelPortonePayment($order, $rf)
    {
        $apiSecret = config('payment.portone.api_secret');
        $paymentId = $order->order_seq; // 주문번호가 결제ID
        
        $body = [
            'reason' => $rf->refund_reason ?? '어드민 환불 처리',
            'amount' => (int) $rf->refund_price
        ];

        // 가상계좌인 경우 환불 계좌 정보 추가
        if ($order->payment === 'virtual') {
            $portoneBankMap = [
                '농협' => 'NONGHYUP', '국민' => 'KOOKMIN', '신한' => 'SHINHAN', '우리' => 'WOORI', '하나' => 'HANA',
                '기업' => 'IBK', '외환' => 'KEB', 'SC제일' => 'SC', '부산' => 'BUSAN', '대구' => 'DAEGU',
                '광주' => 'KWANGJU', '전북' => 'JEONBUK', '경남' => 'KYONGNAM', '강원' => 'KANGWON', '제주' => 'JEJU',
                '우체국' => 'POST', '새마을금고' => 'MG', '신협' => 'CU', '수협' => 'SUHYUP', '상호저축' => 'SAVINGS',
                '카카오뱅크' => 'KAKAOBANK', '토스뱅크' => 'TOSS', '케이뱅크' => 'K_BANK', 'SC제일은행' => 'SC',
                'NH농협' => 'NONGHYUP', 'KB국민은행' => 'KOOKMIN', 'NH농협은행' => 'NONGHYUP', '우체국예금' => 'POST'
            ];

            $bankNameInput = trim($rf->bank_name);
            $mappedBank = null;

            foreach ($portoneBankMap as $key => $val) {
                if (mb_strpos($bankNameInput, $key) !== false || mb_strpos($key, $bankNameInput) !== false) {
                    $mappedBank = $val;
                    break;
                }
            }

            if (!$mappedBank) {
                $mappedBank = 'NONGHYUP'; // 매핑 실패 시 농협 기본값(혹은 오류 처리)
            }

            $body['refundAccount'] = [
                'bank' => $mappedBank,
                'accountNumber' => preg_replace('/[^0-9]/', '', $rf->bank_account),
                'holderName' => trim($rf->bank_depositor)
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.portone.io/payments/" . urlencode($paymentId) . "/cancel",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Authorization: PortOne ' . $apiSecret,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 && $response) {
            return ['success' => true];
        }

        $msg = '환불 API 서버 오류';
        if ($response) {
            $data = json_decode($response, true);
            $msg = $data['message'] ?? $msg;
        }

        return ['success' => false, 'message' => $msg];
    }
}
