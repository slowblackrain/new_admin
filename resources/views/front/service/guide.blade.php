@extends('layouts.front')

@section('content')
    <style>
        body {
            background-color: white;
        }

        #personal.container {
            background-color: white;
            margin: 0 auto;
            padding: 70px 40px;
        }

        #personal,
        #personal a,
        #personal p,
        #personal span,
        #personal div,
        #personal button,
        #personal label,
        #personal li,
        #personal dd,
        #personal dt,
        #personal td,
        #personal th,
        #personal h1,
        #personal h2,
        #personal h3,
        #personal h4,
        #personal h5,
        #personal h6 {
            font-family: '맑은고딕', 'Malgun Gothic', sans-serif;
            letter-spacing: 0;
            word-spacing: -0.025em;
        }

        #personal h4.title {
            border-bottom: 4px double #333;
            font-size: 20px;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .cententsWrap {
            font-size: 13px;
            line-height: 1.8;
            padding-top: 10px;
        }

        #personal .section~.section {
            margin-top: 25px;
        }

        #personal .section h4 {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        #personal .section h5 {
            font-size: 13px;
            font-weight: bold;
            margin: 8px 0 5px
        }

        #personal .section p {
            font-size: 13px;
            margin-top: 7px;
        }

        #personal .section .alert {
            background-color: #f7f8f9;
            padding: 15px;
            font-weight: bold;
            border: 2px solid #f2f3f4;
        }

        #personal .section ul {
            background-color: #f7f8f9;
            padding: 13px;
        }

        #personal .section li {
            position: relative;
            padding: 0 15px;
        }

        #personal .section li:before {
            content: '-';
            position: absolute;
            left: 2px;
        }
    </style>

    <div id="personal" class="container">
        <h4 class="title">이용안내</h4>
        <div class="cententsWrap">
            <div class="inner">
                <div class="section">
                    <h4>1. 회원가입안내</h4>
                    <p>
                        ① {{ config('app.name') }}는 회원제로 운영하고 있습니다. <BR>
                        ② 회원가입비나 회비 등 어떠한 비용도 청구되지 않습니다.<BR>
                        ③ 비회원은 구매는 가능하나 회원등급에 따른 할인 및 적립금 혜택을 받지 못합니다.
                    </p>
                </div>
                <div class="section">
                    <h4>2. 적립금제도</h4>
                    <p>
                        ① 회원등급에 따른 할인금액을 적립금으로 되돌려드립니다.<br>
                        ② 적립금은 10원 이상이면 사용하실 수가 있습니다.<br>
                        ③ 적립금은 상품 구매 시 현금처럼 이용하실 수 있습니다.
                    </p>
                </div>
                <div class="section">
                    <h4>3. 상품주문방법</h4>
                    <p>
                        {{ config('app.name') }}에서 상품을 주문하는 방법은 다음과 같습니다.<BR> <BR>
                        ① 상품검색<BR>
                        ② 장바구니에 담기<BR>
                        ③ 회원ID 로그인 또는 비회원 주문<BR>
                        ④ 결제방법 선택 및 결제<BR>
                        ⑤ 주문 성공 화면 (주문번호)<BR>
                    <p class="alert">※ 비회원 주문인 경우 주문번호와 승인번호(카드결제시)를 꼭 메모해 두시기 바랍니다. (단, 회원인 경우 자동 저장되므로 따로 관리하실 필요가 없습니다.)
                    </p>
                    </p>
                </div>

                <div class="section">
                    <h4>4. 상품종합안내</h4>
                    <p>
                        {{ config('app.name') }}의 상품은 크게 두 가지로 분류됩니다. <br>
                        <b>1. 모델번호가 GT로 시작되는 상품 </b> ex) GTS0739 헐크자명종시계<br>
                        &nbsp;&nbsp; - 국내에 재고를 갖고 있는 상품으로 주문 뒤 배송이 2일~5일 내로 이뤄집니다.<br>
                        <b>2. 모델번호가 AT로 시작되는 상품</b> ex) ATS47964 2p 다용도 접착 후크 걸이(5cm×11cm)<br>
                        &nbsp;&nbsp; - 중국지사 및 대리점, 공장에 재고를 갖고 있는 상품으로 주문 뒤 7-10일후 한국지사에 입고되어 배송됩니다.
                    </p>
                </div>

                <div class="section">
                    <h4>5. 주문확인 및 실시간 배송조회시스템</h4>
                    <p>
                        고객께서 주문/배송 조회란을 통해서 주문 상품의 처리과정을 확인하실 수 있습니다.<br>
                        회원페이지에서 주문/배송 확인을 클릭해 보세요. <br>
                        비회원으로 주문하셨을 경우, 주문번호를 입력하시고 주문내역상세보기의 배송추적을 클릭하세요. (주문 시 입력했던 이름을 정확하게 입력하셔야 합니다.)<br>
                        배송은 한진 택배를 이용하고 있습니다. 본 서비스는 상품 추적을 통해 상품이 어디쯤 도착해 있는지 실시간으로 추적하실 수 있습니다.<br>
                    </p>
                </div>

                <div class="section">
                    <h4>6. 안전한 대금 결제 시스템</h4>
                    <p>
                        {{ config('app.name') }}는 가상계좌, 실시간 계좌이체, 신용카드, 핸드폰 결제방법을 사용합니다. <br>
                        신용카드 결제는 KCP 전자결제를 이용함으로 보안문제는 걱정하지 않으셔도 됩니다. <br>
                        고객님의 이용내역서에는 KCP으로 기록됩니다. <br>
                        이용 가능한 국내 발행 신용카드 - 국내발행 모든 신용카드 <br>
                        이용 가능한 해외 발생 신용카드 - VISA Card, MASTER Card, AMEX Card <br>
                        입금자의 이름은 주문 시 입력하신 입금자와 동일해야 합니다.<br>
                        동일하지 않을 경우 입금확인이 되지 않습니다.
                    </p>
                </div>

                <div class="section">
                    <h4>7. 배송기간 및 배송방법</h4>
                    <p>
                        주문 접수 및 입금확인이 이뤄진 후로부터 2-3일 이내에(최장 9일 이내) 입력하신 배송처로 주문상품이 도착하게 됩니다. (주문하신 상품에 따라 배송기간이 상이할 수 있습니다.)
                        <br>
                        주문하실 때 희망 배송일자를 넉넉히 잡아주시면(3일 이상) 원하시는 날짜에 배송될 수 있도록 최선을 다하겠습니다. <br>
                        저희 {{ config('app.name') }}는 <b>한진 택배 서비스</b>를 이용하고 있습니다. (배송방법은 상품 종류에 따라 상이할 수 있습니다.)
                    </p>
                </div>

                <div class="section">
                    <h4>8. 주문취소, 교환 및 환불</h4>
                    <p>
                        {{ config('app.name') }}는 소비자를 위해 규정한 제반 법규를 준수합니다. <br>
                        주문 취소는 주문접수, 입금확인 단계에서만 가능하며 고객센터로 문의해 주시기 바랍니다. <br>
                        무통장 입금의 경우, 일정기간동안 송금을 하지 않으면 자동 주문 취소가 됩니다.<br>
                        카드 결제의 경우, 승인 취소를 해드리며 취소가 불가능한 경우 환불해드립니다.<br>
                    <h5>교환 및 반품이 가능한 경우</h5>
                    <ul>
                        <li>상품을 공급받고 하자가 발생된 경우 (포장이 훼손되어 상품가치가 상실되면 교환/반품이 불가능합니다.)</li>
                        <li>공급받으신 상품 및 용역의 내용이 표시, 광고 내용과 다른 경우 (교환, 반품은 필히 공급받은 날로부터 7일 이내 하셔야 합니다.)</li>
                    </ul>

                    <h5>교환 및 반품이 불가능한 경우</h5>
                    <ul>
                        <li>도매 특성상 하자가 있는 제품을 제외하곤 반품이 불가합니다.</li>
                        <li>고객님의 책임 있는 사유로 상품이 멸실 또는 훼손된 경우. (단, 상품의 내용을 확인하기 위하여 포장 등을 훼손한 경우는 제외)</li>
                        <li>포장을 개봉하였거나 포장이 훼손되어 상품가치가 상실된 경우
                            &nbsp;&nbsp;(예 : 가전제품, 식품, 음반 등, 단 액정화면이 부착된 노트북, LCD모니터, 디지털 카메라 등의 불량화소에 따른 반품/교환은 제조사 기준에
                            따릅니다.)</li>
                        <li>고객님의 사용 또는 일부 소비에 의하여 상품의 가치가 현저히 감소한 경우 (단, 화장품등의 경우 시용제품을 제공한 경우에 한 합니다.)</li>
                        <li>시간의 경과에 의하여 재판매가 곤란할 정도로 상품의 가치가 현저히 감소한 경우</li>
                        <li>복제가 가능한 상품의 포장을 훼손한 경우 (자세한 내용은 고객만족센터 1:1 E-MAIL상담을 이용해 주시기 바랍니다.)</li>
                    </ul>

                    <h5>※주의사항</h5>
                    <ul>
                        <li>고객님의 단순 변심으로 교환, 반품을 하실 경우 상품반송 비용 일체를 고객님께서 부담하셔야 합니다. (색상 교환, 사이즈 교환 등 포함)</li>
                        <li>반송하실 때는 주문번호, 회원번호를 메모하여 보내주시면 보다 신속한 처리에 도움이 됩니다.</li>
                    </ul>
                    <p class="alert">반송 주소 : {{ config('app.logistics_address') }}</p>
                    </p>
                </div>


            </div>
        </div>
    </div>
@endsection