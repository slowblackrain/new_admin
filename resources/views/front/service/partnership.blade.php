@extends('layouts.front')

@section('content')
    <style>
        /* Legacy Layout Fixes */
        .doto-member-bg {
            padding: 40px 0;
        }

        .alert-tit h2 {
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .contentsBox {
            margin-bottom: 30px;
        }

        .contentsBox h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .contentsBox ul li {
            padding: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .contentsBox p {
            font-size: 14px;
            line-height: 1.6;
        }
    </style>

    <div class="doto-member-bg">
        <div id="doto_join" class="container mbg contact">
            <div class="alert-tit">
                <h2>제휴 안내</h2>
                <p>도매토피아는 귀사의 제휴와 제안을 환영합니다.<br>창의적이고 독창적인 상품, 아이디어, 사업모델을 가지고 계신 분은 제안메일을 보내주세요.</p>
            </div>
            <div class="contentsBox">
                <h3>제안 절차</h3>
                <ul>
                    <li>① 제휴 및 제안 접수</li>
                    <li>② 담당자 검토: 2~3일 정도 소요</li>
                    <li>③ 연락: 좋은 제안에 대해 연락을 드립니다.</li>
                    <li>④ 채택 및 실행: 채택이 되면 계약을 체결하고 실행을 합니다.</li>
                </ul>
            </div>
            <div class="contentsBox">
                <h3>접수방법</h3>
                <p>- 이메일: <strong>{{ config('app.company_email', 'help@dometopia.com') }}</strong></p>
            </div>

            <div class="text-center" style="margin-top: 30px;">
                <a href="{{ route('service.cs') }}" class="btn btn-primary btn-lg">고객센터 문의하기</a>
            </div>
        </div>
    </div>
@endsection