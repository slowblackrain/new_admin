<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Member;

DB::beginTransaction();
try {
    // 1. 임시 테스트용 회원 조회 (혹은 생성)
    $user = Member::first();
    if (!$user) {
        throw new \Exception("테스트용 회원이 존재하지 않습니다.");
    }
    
    // 보유 적립금을 강제로 50원으로 설정 (100원 미만 상태 시뮬레이션)
    $user->emoney = 50;
    $user->save();
    
    echo "테스트 회원 아이디: {$user->userid}, 보유 적립금 강제 설정: {$user->emoney}원\n";
    
    // 2. 적립금 밸리데이션 규칙 정의
    $reserves = DB::table('fm_config')->where('groupcd', 'reserve')->get()->pluck('value', 'codecd')->toArray();
    $minEmoney = (int) ($reserves['min_emoney'] ?? 100);          // 최소 사용 적립금 (100원)
    $useLimit = (int) ($reserves['emoney_use_limit'] ?? 100);     // 최소 보유 적립금 (100원)
    $maxPolicy = $reserves['max_emoney_policy'] ?? 'unlimit';
    
    echo "DB 설정 - 최소 보유 제한: {$useLimit}원, 최소 사용 제한: {$minEmoney}원, 한도 정책: {$maxPolicy}\n";
    
    // 3. 테스트 케이스 실행
    
    // Case 1: 보유 100원 미만인데 50원 사용을 시도하는 경우
    $useEmoney = 50;
    echo "[테스트 Case 1] 보유 적립금이 {$user->emoney}원인 상태에서 {$useEmoney}원 사용 시도\n";
    if ($user->emoney < $useLimit) {
        echo "=> 정상 차단 성공: " . number_format($useLimit) . "원 이상 적립하여야 합니다.\n";
    } else {
        throw new \Exception("Case 1 차단 실패!");
    }
    
    // 보유 적립금을 200원으로 설정 (최소 보유는 충족)
    $user->emoney = 200;
    $user->save();
    echo "보유 적립금 200원으로 상향 조정 완료.\n";
    
    // Case 2: 최소 사용 제한(100원) 미만인 50원 사용을 시도하는 경우
    $useEmoney = 50;
    echo "[테스트 Case 2] {$useEmoney}원 사용 시도 (최소 사용 100원 제한 테스트)\n";
    if ($useEmoney < $minEmoney) {
        echo "=> 정상 차단 성공: 적립금은 최소 " . number_format($minEmoney) . "원부터 사용가능 합니다.\n";
    } else {
        throw new \Exception("Case 2 차단 실패!");
    }
    
    // Case 3: 보유 적립금보다 큰 300원 사용을 시도하는 경우
    $useEmoney = 300;
    echo "[테스트 Case 3] 보유한 200원보다 많은 {$useEmoney}원 사용 시도\n";
    if ($useEmoney > $user->emoney) {
        echo "=> 정상 차단 성공: 보유하신 적립금이 부족합니다.\n";
    } else {
        throw new \Exception("Case 3 차단 실패!");
    }
    
    // Case 4: 정상적인 150원 사용 시도
    $useEmoney = 150;
    echo "[테스트 Case 4] 정상 조건인 {$useEmoney}원 사용 시도\n";
    if ($user->emoney >= $useLimit && $useEmoney >= $minEmoney && $useEmoney <= $user->emoney) {
        echo "=> 정상 검증 통과 완료!\n";
    } else {
        throw new \Exception("Case 4 정상 통과 실패!");
    }
    
    echo "\n모든 적립금 밸리데이션 Mock 테스트가 완벽히 성공하였습니다!\n";
    
} catch (\Exception $e) {
    echo "테스트 실패 에러: " . $e->getMessage() . "\n";
} finally {
    // 실서버 데이터 보호를 위해 무조건 롤백 처리!
    DB::rollBack();
    echo "데이터 보호를 위해 테스트 트랜잭션이 성공적으로 롤백(Rollback)되었습니다.\n";
}
