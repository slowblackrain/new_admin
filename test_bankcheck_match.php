<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\Order\BankCheckController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Mosms;

// 더미 데이터 생성 스크립트에서 만든 데이터 ID
$targetOrderSeq = '20260224012704BANK';
$targetMosmsIdx = 24620; // 금액 미스매치 건을 수동으로 강제 매칭!

echo "--- Bank Check Match Dummy Test ---\n";

// 1. 상태 확인 (Before)
$orderBefore = Order::find($targetOrderSeq);
$smsBefore = Mosms::find($targetMosmsIdx);

echo "Before Match - Order {$targetOrderSeq} Step: {$orderBefore->step}\n";
echo "Before Match - Mosms {$targetMosmsIdx} Status: {$smsBefore->pg_status}\n";

// 2. 가짜 Admin 세션 로그인 (Auth Guard 우회)
// Auth::guard('admin')->user()->mname 로직 대응을 위해 더미 유저 생성 후 로그인
// 관리자 모델이 fm_member 인지 다른 것인지 컨트롤러를 보면, admin 가드를 사용함.
// 테스트 용이므로 $sms->memo 처리에 문제가 생길 수 있어, 임시 Mock 처리하거나 Request 객체를 만들어 보냄.

// 컨트롤러 인스턴스화
$controller = new BankCheckController();

// Request 객체 생성 (모방)
$request = Request::create('/admin/order/bank_check/process', 'POST', [
    'mosms_idx' => $targetMosmsIdx,
    'order_seq' => $targetOrderSeq
]);

// 잠시 Admin 권한 Mocking이 복잡하므로, Controller 코드를 직접 재현하거나 
// 임시로 관리자 로그인을 시켜야 함. DB에서 실제 관리자 계정을 찾아 로그인.
$admin = \Illuminate\Support\Facades\DB::table('fm_manager')->first();
if ($admin) {
    echo "Simulating Admin Login: {$admin->manager_id}\n";
    \Illuminate\Support\Facades\Auth::guard('admin')->loginUsingId($admin->manager_seq);
} else {
    echo "Warning: Admin user not found. processMatch might fail at Auth::user().\n";
}

// 3. 매칭 실행
try {
    $response = $controller->processMatch($request);
    echo "Response JSON: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error During Match: " . $e->getMessage() . "\n";
}

// 4. 상태 확인 (After)
$orderAfter = Order::find($targetOrderSeq);
$smsAfter = Mosms::find($targetMosmsIdx);

echo "After Match - Order Step: {$orderAfter->step} (Expected: 25)\n";
echo "After Match - Mosms Status: {$smsAfter->pg_status} (Expected: M)\n";

echo "--- Match Test Complete ---\n";
