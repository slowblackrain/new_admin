<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * 알림톡 발송 (스마일서브 iwinv API)
     *
     * @param string $templateCode 템플릿 코드
     * @param string $phone 수신자 번호
     * @param array $templateParams 템플릿 변수 치환 배열
     * @param bool $fallbackToSms 실패 시 SMS 로 대체 발송할지 여부
     * @return array
     */
    public function sendAlimtalk(string $templateCode, string $phone, array $templateParams = [], bool $fallbackToSms = true)
    {
        $config = config('services.iwinv_alimtalk');
        
        // Remove hyphens from phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $payload = [
            'templateCode' => $templateCode,
            'reSend' => $fallbackToSms ? 'Y' : 'N',
            'resendCallback' => $config['sender_number'],
            'list' => [
                [
                    'phone' => $phone,
                    'templateParam' => $templateParams
                ]
            ]
        ];

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'AUTH' => base64_encode($config['token'])
            ])
            ->timeout(60)
            ->withOptions([
                'verify' => false // CURLOPT_SSL_VERIFYPEER => false in legacy
            ])
            ->post($config['endpoint'], $payload);

            $result = (string) $response->body(); // Raw response string

            // Log the attempt
            $this->logAlimtalk($templateCode, $phone, $templateParams, $result);

            return [
                'success' => (bool) $response->successful(),
                'status' => (int) $response->status(),
                'response' => $result
            ];

        } catch (\Exception $e) {
            Log::error("Alimtalk Request Failed: " . $e->getMessage(), ['phone' => $phone, 'templateCode' => $templateCode]);
            
            // Log the failure
            $this->logAlimtalk($templateCode, $phone, $templateParams, 'Error: ' . $e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 알림톡 전송 내역 자체 로그 기록 (fm_log_message)
     */
    private function logAlimtalk($templateCode, $phone, $params, $resultRaw)
    {
        try {
            DB::table('fm_log_message')->insert([
                'regist_date' => now(),
                'temp_code' => $templateCode,
                'call_number' => $phone,
                'arr_msg' => json_encode($params, JSON_UNESCAPED_UNICODE),
                // Additional fields can be mapped here if needed to store raw result
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to insert Alimtalk log: " . $e->getMessage());
        }
    }

    /**
     * 일반 SMS 발송 (스마일서브 iwinv API)
     *
     * @param string $phone
     * @param string $message
     * @param int|null $senderManagerSeq 발송자 관리자 시퀀스 (Optional)
     * @return array
     */
    public function sendSms(string $phone, string $message, $senderManagerSeq = null)
    {
        // Remove hyphens from phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 스팸 / 중복 전송 방지 (최근 60초 내 동일 번호, 동일 메시지)
        $recentDuplicate = DB::table('fm_log_sms')
            ->where('call_number', $phone)
            ->where('msg', $message)
            ->where('regist_date', '>=', now()->subSeconds(60))
            ->exists();

        if ($recentDuplicate) {
            return [
                'success' => false,
                'code' => '0000',
                'message' => '중복발송 방지 (60초 쿨타임)',
                'is_duplicate' => true
            ];
        }

        $config = config('services.iwinv_sms');
        $secret = base64_encode($config['api_key'] . "&" . $config['auth_key']);

        $payload = [
            'version' => '2.0',
            'from' => $config['sender_number'],
            'to' => [$phone],
            'text' => $message
        ];

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'secret' => $secret
            ])
            ->timeout(60)
            ->withOptions([
                'verify' => false
            ])
            ->post($config['endpoint'], $payload);

            $responseData = $response->json();
            
            $success = (bool) $response->successful() && isset($responseData['resultCode']) && $responseData['resultCode'] == 0;
            $code = $responseData['resultCode'] ?? 'ERROR';
            if ($code == 0) $code = '0000'; // Legacy normalization

            // Log the SMS
            if ($response->successful()) {
                 $this->logSms(
                    $code,
                    $responseData['requestNo'] ?? null,
                    $responseData['message'] ?? '',
                    $message,
                    $responseData['msgType'] ?? '',
                    $phone,
                    $senderManagerSeq
                 );
            }

            return [
                'success' => $success,
                'code' => $code,
                'message' => $responseData['message'] ?? 'Unknown Error',
                'msgType' => $responseData['msgType'] ?? '',
                'raw' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error("SMS Request Failed: " . $e->getMessage(), ['phone' => $phone]);
            return [
                'success' => false,
                'code' => '500',
                'message' => $e->getMessage(),
                'is_duplicate' => false
            ];
        }
    }

    /**
     * SMS 전송 내역 자체 로그 기록 (fm_log_sms)
     */
    private function logSms($code, $requestNo, $resultMsg, $sentMsg, $msgType, $phone, $senderSeq = null)
    {
        try {
            DB::table('fm_log_sms')->insert([
                'result_cd' => $code,
                'result_no' => $requestNo,
                'result_msg' => ltrim($resultMsg, "\0..\37"),
                'regist_date' => now(),
                'msg' => $sentMsg,
                'mtype' => $msgType,
                'sender' => $senderSeq ?? 0,
                'call_number' => $phone
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to insert SMS log: " . $e->getMessage());
        }
    }
}
