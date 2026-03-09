<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ForceTransactionRollback
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 로컬 환경이고, GET/HEAD/OPTIONS 가 아닌 경우(POST, PUT, DELETE 등 쓰기 작업)에만 롤백 작동
        // 그리고 이 미들웨어를 거치는 라우트들을 대상으로 함.
        if (app()->environment('local') && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            DB::beginTransaction();

            try {
                $response = $next($request);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // 강제 롤백 (실제 INSERT, UPDATE 등 방지)
            DB::rollBack();

            // 세션에 안전 모드 안내 문구 삽입
            if ($request->hasSession()) {
                $request->session()->flash('alert-warning', '[SAFE MODE] DB 변경 작업이 성공적으로 시뮬레이션 된 후 자동 롤백되었습니다. 라이브 데이터는 변경되지 않았습니다.');
            }

            return $response;
        }

        return $next($request);
    }
}
