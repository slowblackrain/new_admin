@extends('layouts.front')

@section('content')
    <link href="/css/legacy/about_doto.css" rel="stylesheet">
    <!--회사소개 content-->

    <section id="top" class="about_visual">
        <div class="container inner">
            <div class="page-title clear">

                <p>
                    대한민국 B2B의 시작<br />
                    전자상거래 전문 쇼핑몰
                </p>
                <span>도매토피아</span>
            </div>
        </div>
    </section>

    <nav class="navigation">
        <ul>
            <li><a href="#section1">인사말</a></li>
            <li><a href="#section2">도매토피아</a></li>
            <!--  <li><a href="#section3">조직도</a></li> -->
            <li><a href="#section4">연혁</a></li>
            <li><a href="#section5">채용공고</a></li>
        </ul>
    </nav>

    <section id="section1" class="top-down-130">
        <div class="container inner">
            <div class="greet titles">
                <div class="title">
                    <span class="number"></span>
                    <h2>인사말</h2>
                </div>
                <h3>
                    세상 모든 제품을 이해할 순 없어도<br /> 세상 모든 상품을 거래하려는 기업!
                </h3>
                <p>전자상거래가 낯설던 대한민국 2002년</p>
                <p>
                    초콜릿 수입으로 첫 사업을 시작한 도매토피아는
                    국내 특정 지역 오프라인 도매업 대상이던 납품 사업을 <br />
                    전국 도소매상으로 확대하기 위해 온라인 B2B몰을 오픈하였습니다.
                </p>
                <p>
                    온라인 첫 상호인 천냥하우스가 대중에게 알려지기까지
                    중국 직수입부터 배송대행 개발, 전자상거래 마케팅 <br />연구 등
                    항상 몸과 머리로 부딪히며 하루하루 숨 가쁘게 달려왔습니다.
                </p>
                <p>
                    지나고 나니 그 젊음은 아름답고 찬란했다, 라는 말을 되새기며 <br />
                    이제 도매토피아는 제 2의 도약을 준비하고 있습니다.
                </p>
                <p>
                    대한민국 B2B 전자상거래의 대표 주자라는 자존심을 되새기며
                    국내외에 숨겨진 상품 발굴과<br /> 글로벌 마켓 교두보를 세우기 위해
                    도매토피아 임직원 모두는 항상 최선의 노력을 다하겠습니다.
                </p>
                <div class="ceo">
                    <h4>대표<span class="name"></span>
                        <!--<img src="images/doto_about/ceo_img.png">-->
                    </h4>
                </div>
            </div>
        </div>

    </section>
    <section style="text-align: center; background-color: #ebeff5; padding: 45px 0;">
        <div class="youtube_mov">
            <iframe width="854" height="480"
                src="https://www.youtube.com/embed/8esGiRDy__k?autoplay=1&mute=1&playlist=8esGiRDy__k&loop=1"
                title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        </div>
    </section>
    <section id="section2" class="top-down-130">
        <div class="container inner">
            <div class="overview clear">
                <div class="fl-l keyvisual"></div>
                <div class="fl-l titles">
                    <div class="title">
                        <span class="number"></span>
                        <h2>도매토피아</h2>
                    </div>
                    <h3>대한민국 최대 B2B몰</h3>
                    <p style="margin-bottom:20px;">대한민국 최대 B2B몰을 운영하는 도매토피아는 3만 여종의 생활용품을 직수입 직도매 직발송을 통해 공급하고 국내외 PB/OEM
                        생산, 수출입 대행, ATS투자상품 판매대행, B2B 배송대행, 물류관리 서비스, 글로벌 마켓 입점 등 무역, 도매, 물류 사업을 진행하고 있습니다.</p>
                    <p>연봉 1억 B2B판매자 육성을 위하여 도토창업센터를 설립하여 쇼핑몰 창업교육과 실전 강의를 제공하고 오픈마켓 연동 쇼핑몰 ‘샵온’을 분양하며 디자인 콘텐츠 제작 등 다양한 판매
                        컨텐츠를 제작 판매 교육하고 있습니다.</p>
                </div>
            </div><!--overview end-->

            <div class="contents">
                <ul>
                    <li>
                        <div><i class="fas fa-boxes"></i></div>
                        <p>30,000품목 생활용품</p>
                    </li>
                    <li>
                        <div><i class="far fa-copy"></i></div>
                        <p>국내외 수출입</p>
                    </li>
                    <li>
                        <div><i class="fas fa-box-open"></i></div>
                        <p>직수입 직도매 직발송</p>
                    </li>
                    <li>
                        <div><i class="fas fa-dolly-flatbed"></i></div>
                        <p>B2B 배송대행</p>
                    </li>
                    <li>
                        <div><i class="far fa-file-alt"></i></div>
                        <p>국내외 PB/OEM</p>
                    </li>
                    <li>
                        <div><i class="fas fa-cart-arrow-down"></i></div>
                        <p>중국 시장조사</p>
                    </li>
                    <li>
                        <div><i class="fas fa-power-off"></i></div>
                        <p>통합창업서비스 ‘샵온’</p>
                    </li>

                    <li>
                        <div><i class="fas fa-exchange-alt"></i></div>
                        <p>오픈마켓 연동 솔루션</p>
                    </li>

                    <li>
                        <div><i class="fas fa-signal"></i></div>
                        <p>도메인 호스팅 로그분석</p>
                    </li>

                    <li>
                        <div><i class="fas fa-globe"></i></div>
                        <p>글로벌 마켓입점</p>
                    </li>
                    <li>
                        <div><i class="far fa-handshake"></i></div>
                        <p>영업대행 입점 위탁</p>
                    </li>
                    <li>
                        <div><i class="far fa-comments"></i></div>
                        <p>ATS투자상품</p>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section id="section4" class="top-down-130">
        <div class="container inner">
            <div class="history titles">
                <div class="title">
                    <span class="number"></span>
                    <h2>연혁</h2>
                </div>
            </div>
            <div class="contents">
                <ul class="year">
                    <li>2002-2007</li>
                    <li>2008-2013</li>
                    <li>2014-현재</li>
                </ul>
                <div class="achieve">
                    <ul>
                        <li>
                            <dl>
                                <dt>2002</dt>
                                <dd>G&T무역 창업</dd>
                            </dl>
                        </li>
                    </ul>
                    <!-- ... truncated for brevity ... -->
                </div>
            </div>
        </div>
    </section>

    <section id="section5" class="top-down-130">
        <div class="container inner">
            <div class="titles">
                <div class="title">
                    <span class="number"></span>
                    <h2>채용공고</h2>
                    <p>회사와 목표를 공유하고 동반 성장할 수 있는 우수한 인재의 지원을 기다립니다.</p>
                </div>
            </div>
            <!-- ... truncated ... -->
        </div>
    </section>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('.navigation a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
@endsection