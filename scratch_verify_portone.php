<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRefund;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Front\PaymentController;
use App\Http\Controllers\Admin\Order\ClaimController;
use Illuminate\Http\Request;

echo "=== START PORTONE PG INTEGRATION TEST ===\n";

DB::beginTransaction();
try {
    // 1. Mock Order Creation (Initially setting pg to 'portone' to trigger validation)
    $orderSeq = '9999' . date('ymdHis');
    $order = new Order();
    $order->order_seq = $orderSeq;
    $order->settleprice = 12000;
    $order->step = 15;
    $order->payment = 'virtual';
    $order->order_user_name = '홍길동';
    $order->pg = 'portone';
    $order->save();
    
    // Mock OrderItem Creation
    $orderItem = new OrderItem();
    $orderItem->order_seq = $orderSeq;
    $orderItem->goods_seq = 12345;
    $orderItem->goods_name = '테스트 일반 상품';
    $orderItem->goods_code = 'MOCK001';
    $orderItem->save();
    
    echo "1. Mock Order & Item Created: {$orderSeq}\n";

    // 2. Test determinePg method
    $paymentController = new PaymentController();
    
    $reflection = new \ReflectionClass(PaymentController::class);
    $method = $reflection->getMethod('determinePg');
    $method->setAccessible(true);
    
    $pgParams = $method->invokeArgs($paymentController, [$order]);
    echo "2. determinePg result:\n";
    print_r($pgParams);
    
    if ($pgParams['pg'] === 'portone' && isset($pgParams['channelKeyVbank'])) {
        echo "   -> [SUCCESS] PG mapped to PortOne and contains keys.\n";
    } else {
        throw new \Exception("determinePg validation failed!");
    }

    // 3. Test virtual account validation on admin refund
    // Create a mock OrderRefund
    $refundCode = 'RF' . date('ymdHis');
    $refund = new OrderRefund();
    $refund->refund_code = $refundCode;
    $refund->order_seq = $orderSeq;
    $refund->refund_price = 5000;
    $refund->status = 'request';
    $refund->bank_name = ''; // Empty to trigger validation failure
    $refund->bank_account = '';
    $refund->bank_depositor = '';
    $refund->save();
    
    echo "3. Mock OrderRefund Created: {$refundCode}\n";

    $claimController = new ClaimController();
    $mockRequest = Request::create('/admin/order/claim/process', 'POST', [
        'type' => 'refund',
        'codes' => [$refundCode],
        'status' => 'complete'
    ]);
    
    echo "4. Simulating Claim Complete Process (with empty bank details for virtual payment)...\n";
    $response = $claimController->process($mockRequest);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response:\n";
    print_r($responseData);
    
    if ($responseData['success'] === false && strpos($responseData['message'], '정보가 누락되었습니다') !== false) {
        echo "   -> [SUCCESS] Enforced bank validation correctly caught the missing details.\n";
    } else {
        throw new \Exception("Virtual bank validation failed to intercept!");
    }

    // Update bank details to test success flow bypass (since we are on local and don't call real PortOne cancel for mock/empty tid)
    $refund->bank_name = '농협';
    $refund->bank_account = '123-456-789';
    $refund->bank_depositor = '홍길동';
    $refund->save();
    
    // 5. Test success with correct bank info, but let's see how it behaves.
    // Since order->pg is 'portone', it will trigger the PortOne API cancellation.
    // real API call will fail because we have no real transaction, which is expected.
    echo "5. Simulating Claim Complete with bank details but no transaction (PortOne API cancel trigger)...\n";
    $response = $claimController->process($mockRequest);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response:\n";
    print_r($responseData);
    
    if ($responseData['success'] === false && strpos($responseData['message'], '포트원 결제 취소 API 승인 실패') !== false) {
        echo "   -> [SUCCESS] PortOne API cancellation was successfully triggered and failed gracefully as expected.\n";
    } else {
        throw new \Exception("PortOne API trigger behavior was unexpected!");
    }

    echo "=== ALL INTEGRATION VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";

} catch (\Exception $e) {
    echo "!!! TEST FAILED: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
    echo "=== DB ROLLBACK COMPLETE. TEST CLEANUP OK. ===\n";
}
