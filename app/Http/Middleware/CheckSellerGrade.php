<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSellerGrade
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredGrade
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredGrade = 'Y')
    {
        $seller = Auth::guard('seller')->user();

        if (!$seller) {
            return redirect()->route('seller.login');
        }

        // provider_YN == 'Y' indicates '판매대행' (B2B Agency) which the legacy code checked as gubun_p
        $provider_yn = $seller->member->provider_YN ?? 'N';
        
        if ($provider_yn !== $requiredGrade) {
             if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->route('seller.dashboard')->with('error', '해당 메뉴에 접근할 권한이 없습니다. (판매대행사 전용)');
            }
        }

        return $next($request);
    }
}
