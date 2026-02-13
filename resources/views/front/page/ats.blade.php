@extends('layouts.front')

@section('content')
<link href="{{ asset('css/m_sub_page.css') }}" rel="stylesheet">
<style>
/* Inline critical CSS from m_sub_page.css if needed, or assume it's loaded */
/* Basic reset/layout for ATS page */
#ats img { max-width: 100%; height: auto; }
.contain { padding: 0 15px; margin: 0 auto; max-width: 100%; box-sizing: border-box; }
.section_tit { text-align: center; font-size: 22px; font-weight: bold; margin: 30px 0 20px; color: #333; }
.section_tit b { color: #d32f2f; }
.btn_area { text-align: center; margin-bottom: 30px; }
.btn_area a { display: block; background: #333; color: #fff; padding: 15px; margin-bottom: 10px; text-decoration: none; font-weight: bold; }
.btn_area a:hover { background: #555; }
.tel { text-align: center; margin-top: 20px; font-size: 16px; }
.tel a { text-decoration: none; color: #333; font-weight: bold; }
</style>

<div id="ats">
    <img src="{{ asset('images/legacy/etc/main01.jpg') }}" width="100%">

    <br><br><br>
    
    <h2 class="section_tit"> <b>판매대행 서비스</b> 설명회 동영상</h2>
    <div class="youtube_mov" style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; max-width:100%;">
        <iframe style="position:absolute; top:0; left:0; width:100%; height:100%;" src="https://www.youtube.com/embed/sOwS6AnfsEc?autoplay=0&mute=1&playlist=sOwS6AnfsEc&loop=1" title="[도토 TV] 판매대행 라이브 하이라이트" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="youtube_mov" style="text-align:center; margin-top:10px;">
        <a href="https://dometopia.com/board/view?id=ATSInvestEnter&seq=6127861" target="_blank" style="display:inline-block; padding:10px 20px; background:#d32f2f; color:#fff; text-decoration:none;">사업 설명회 신청하기 <i class="fas fa-arrow-alt-circle-right"></i></a>
    </div>

    <section class="section_02">
        <div class="contain">
            <h2 class="section_tit"> 대한민국 최초! 쉽고 간단한 <b>판매대행</b>!</h2>
            <div class="bg">
                <img src="{{ asset('images/legacy/etc/m_ats.jpg') }}" alt="ATS Service Info">
            </div>
        </div>
    </section>

    <section class="section_02" style="background:#f9f9f9; padding:20px 0;">
        <div class="contain">
             <h2 class="section_tit">이익은 높게! 운영은 쉽게! 리스크 제로!<br><b>실패없는 판매대행</b>!</h2>
             {{-- Replaced complex list with image for simplicity if layout matches images --}}
             <img src="{{ asset('images/etc/m_business_bg.jpg') }}" onerror="this.style.display='none'" width="100%">
        </div>
    </section>

    <h2 class="section_tit" style="margin-top:25px;"> <b>판매대행 서비스</b> 이용절차</h2>

    <div class="m_ats_all" style="text-align:center;">
        <div class="m_ats_step">
            <a href="{{ route('member.agreement') }}"><img src="{{ asset('images/legacy/etc/m_step_01.jpg') }}" class="m_sub_step"></a>
            <a href="#"><img src="{{ asset('images/legacy/etc/m_step_02.jpg') }}" ></a>
        </div>
        <div class="m_ats_step">
             <img src="{{ asset('images/legacy/etc/m_step_03.jpg') }}" class="m_sub_step">
             <img src="{{ asset('images/legacy/etc/m_step_04.jpg') }}">
        </div>
         <div class="m_ats_button_x">
            <img src="{{ asset('images/legacy/etc/m_step_05.jpg') }}" class="m_sub_step">
            <img src="{{ asset('images/legacy/etc/m_step_06.jpg') }}">
        </div>
    </div>

    <section class="section_08" style="background:#eee; padding:30px 0; margin-top:30px;">
        <div class="contain">            
            <div class="btn_area">
                <a href="https://dometopia.com/board/view?id=ATSInvestEnter&seq=6121020">판매대행 설명회 신청하기 <i class="fas fa-arrow-alt-circle-right"></i></a>
                <a href="https://www.youtube.com/watch?v=sOwS6AnfsEc&t=10s" target="_blank">
                판매대행 사업설명회 동영상 시청하기 <i class="fas fa-arrow-alt-circle-right"></i></a>
                <a href="#">판매대행 구좌 구매하기 <i class="fas fa-arrow-alt-circle-right"></i></a>
                <a href="#">판매대행 게시판 문의하기 <i class="fas fa-arrow-alt-circle-right"></i></a>
            </div>
            <h2 class="section_tit"><b>상담 문의하기</b></h2>
            <div class="tel">
                <div style="margin-bottom:10px;"><span><i class="fas fa-phone"></i></span> <a href="tel:02-2624-1976">02-2624-1976</a></div>
                <div><a href="https://pf.kakao.com/_xhdJSxj" target="_blank"><img src="{{ asset('images/legacy/etc/bt_kakao.png') }}" alt="kakao" style="vertical-align:middle; width:20px;" /> 판매대행 소식받기 <i class="fas fa-arrow-alt-circle-right"></i></a></div>
            </div>
            
        </div>
    </section>
</div>
@endsection
