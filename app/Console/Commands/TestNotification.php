<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class TestNotification extends Command
{
    protected $signature = 'test:notification {type : sms or alimtalk} {phone} {--template= : Alimtalk template code} {--message= : SMS message}';
    protected $description = 'Test sending SMS or Alimtalk via iwinv API';

    public function handle(NotificationService $notificationService)
    {
        $type = $this->argument('type');
        $phone = $this->argument('phone');

        if ($type === 'alimtalk') {
            $templateCode = $this->option('template') ?: 'R000000044_21284';
            
            // R000000044_21284: 무통장 입금 안내 템플릿의 변수
            // bank_account, depositor, settleprice, cfr
            $params = [
                '국민은행 123-456-7890 도매토피아',
                '홍길동',
                '50,000',
                '48시간'
            ];
            
            $this->info("Sending Alimtalk to {$phone} with template {$templateCode}...");
            $result = $notificationService->sendAlimtalk($templateCode, $phone, $params);
            
            $this->line("Result:");
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
        } elseif ($type === 'sms') {
            $message = $this->option('message') ?: '도매토피아 시스템 연동 테스트 SMS 발송입니다.';
            $this->info("Sending SMS to {$phone}...");
            
            $result = $notificationService->sendSms($phone, $message);
            
            $this->line("Result:");
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->error("Invalid type. Use 'sms' or 'alimtalk'.");
        }
    }
}
