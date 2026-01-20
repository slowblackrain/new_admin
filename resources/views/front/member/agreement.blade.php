@extends('layouts.front')

@section('content')
    <div class="doto-member-bg" style="background: #fff; padding: 50px 0;">
        <div id="doto_join" class="container" style="width: 900px; margin: 0 auto;">

            <div class="join_tit" style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 30px; font-weight: bold;">회원가입</h2>
                <p style="color: #666;">도매토피아에 오신 것을 환영합니다.</p>
            </div>

            <div class="jointype-wrap" style="display: flex; gap: 20px; justify-content: center;">
                {{-- Business Member --}}
                <div class="type-box"
                    style="width: 30%; border: 1px solid #ddd; padding: 30px; text-align: center; border-radius: 10px; box-shadow: 2px 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 15px; color: #007bff;">기업 회원</h3>
                    <p style="font-size: 14px; color: #555; margin-bottom: 20px;">사업자등록증이 있는 기업 회원<br>(판매/구매)</p>
                    <a href="{{ route('member.register', ['type' => 'business']) }}"
                        style="display: block; padding: 10px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">가입하기</a>
                </div>

                {{-- General Member --}}
                <div class="type-box"
                    style="width: 30%; border: 1px solid #ddd; padding: 30px; text-align: center; border-radius: 10px; box-shadow: 2px 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 15px; color: #28a745;">일반 회원</h3>
                    <p style="font-size: 14px; color: #555; margin-bottom: 20px;">개인 구매를 위한 일반 회원<br>(소매)</p>
                    <a href="{{ route('member.register', ['type' => 'general']) }}"
                        style="display: block; padding: 10px; background: #28a745; color: #fff; text-decoration: none; border-radius: 5px;">가입하기</a>
                </div>
            </div>

        </div>
    </div>
@endsection