@extends('layouts.legacy')

@section('content')
    <div id="main-wrap">
        <div class="main_header">
            <!-- 레거시 사이드 카테고리 (Side Category) -->
            <div class="ctg_top_list">
                <div id="menu2">
                    <ul class="M01">
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_001.png') }}" title="기획전">
                                </div> 기획전
                            </a>
                            <span class="new_sticker_n"></span>
                        </li>
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_002.png') }}" title="신상품">
                                </div> 신상품
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_003.png') }}" title="베스트">
                                </div> 베스트
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_004.png') }}" title="월별베스트">
                                </div> 월별베스트
                            </a>
                            <span class="new_sticker_s"></span>
                        </li>
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_005.png') }}" title="창업센터">
                                </div> 창업센터
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <div class="cta_list_icon">
                                    <img src="{{ asset('images/legacy/new_main/ctg_top_list_006.png') }}" title="이벤트">
                                </div> 이벤트
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 메인 배너 및 알림 섹션 (Main Banner & Notice Section) -->
            <div class="section_main">
                <div id="main_slider" class="mslide"
                    style="overflow: hidden; position: relative; width: 746px; height: 362px;">
                    <ul class="slidelist"
                        style="display: flex; list-style: none; padding: 0; margin: 0; transition: transform 0.5s ease-in-out;">
                        @foreach(range(1, 5) as $i)
                            <li style="flex: 0 0 100%;">
                                <a href="#"><img src="{{ asset('images/legacy/main/images_' . $i . '.jpg') }}"
                                        style="width: 100%; height: auto;"></a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="slide-pagelist" style="position: absolute; bottom: 10px; width: 100%; text-align: center;">
                        @foreach(range(1, 5) as $i)
                            <span class="slide-dot"
                                style="display: inline-block; width: 10px; height: 10px; background: #fff; border-radius: 50%; margin: 0 5px; cursor: pointer; opacity: 0.5;"
                                onclick="moveSlider({{ $i - 1 }})"></span>
                        @endforeach
                    </div>
                </div>

                <script>
                    let currentSlide = 0;
                    const slideCount = 5;
                    const slideList = document.querySelector('.slidelist');
                    const dots = document.querySelectorAll('.slide-dot');

                    function moveSlider(index) {
                        currentSlide = index;
                        slideList.style.transform = `translateX(-${currentSlide * 100}%)`;
                        dots.forEach((dot, i) => dot.style.opacity = i === currentSlide ? '1' : '0.5');
                    }

                    setInterval(() => {
                        currentSlide = (currentSlide + 1) % slideCount;
                        moveSlider(currentSlide);
                    }, 4000);

                    // Set initial active dot
                    dots[0].style.opacity = '1';
                </script>

                <div class="notice">
                    <div class="notice_list">
                        <a href="#"><img src="{{ asset('images/legacy/main/main_top01_new.png') }}" title="직수입할인상품"
                                alt="직수입할인상품"></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 사업부 4가지 타일 배너 (Business Unit Tile Banners) -->
    <div class="bnr_slide1">
        <a href="#"><img src="{{ asset('images/legacy/main/n_bnr_tile01.jpg') }}" title="B2B 대량구매"></a>
        <a href="#"><img src="{{ asset('images/legacy/main/n_bnr_tile03.jpg') }}" title="샵온 창업 아카데미"></a>
        <a href="#"><img src="{{ asset('images/legacy/main/n_bnr_tile04.jpg') }}" title="도토 입점/투자" style="margin-top: -3px;"></a>
        <a href="#"><img src="{{ asset('images/legacy/main/n_bnr_tile02.jpg') }}" title="도토아카데미" style="margin-top: -3px;"></a>
    </div>

    <!-- 주요 카테고리 아이콘 리스트 (Major Category Icons) -->
    <div class="nav_cat_list">
        <ul class="nav_main_list">
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img001.png') }}">BEST100</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img002.png') }}">판촉물</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img004.png') }}">해외직구</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img005.png') }}">신상품</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img006.png') }}">초저가</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img007.png') }}">땡처리</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img003.png') }}">할인특가</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img008.png') }}">인기카테고리</a></li>
            <li><a href="#"><img src="{{ asset('images/legacy/new_main/tit_icon_img009.png') }}">더보기</a></li>
        </ul>
    </div>

    <!-- MD's Choice 섹션 (MD's Choice Section) -->
    <div class="recommend_item" id="category_nav01">
        <div class="marin-tit"><b>MD's Choice</b> <p>이 상품 어때요?</p></div>
        
        <div style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px;">
            @foreach(range(1, 4) as $i)
            <div style="flex: 0 0 280px; border: 1px solid #eee; border-radius: 12px; overflow: hidden; background: #fff; transition: transform 0.3s;">
                <div style="width: 100%; height: 280px; background: #f4f4f4; position: relative;">
                    <img src="https://via.placeholder.com/280x280?text=MD+Choice+{{$i}}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="padding: 15px;">
                    <div style="color: #3ba0ff; font-size: 13px; font-weight: bold; margin-bottom: 5px;">[MD추천]</div>
                    <div style="font-size: 16px; font-weight: bold; color: #333; height: 44px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">레거시 스타일 상품명 {{ $i }} - MD가 추천하는 핫 아이템</div>
                    <div style="margin-top: 10px; display: flex; align-items: baseline; gap: 8px;">
                        <span style="color: #eb5221; font-size: 20px; font-weight: 800;">12,500원</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 랭킹 섹션 (Ranking & Best 100 Section) -->
    <div id="ranking" style="display: flex; gap: 20px; margin-top: 40px;">
        <div class="best100" style="flex: 2; background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 20px;">
            <div class="section_title" style="display: flex; align-items: center; margin-bottom: 20px;">
                <h4 style="margin: 0; display: flex; align-items: center;">
                    <img src="{{ asset('images/legacy/main/section_title_ranking.png') }}" alt="Ranking">
                </h4>
                <p style="color: #9eabbb; font-size: 15px; margin-left: 10px;">어제 많이 판매된 도토 상품</p>
            </div>
            
            <div class="tab_container">
                <ul class="doto-rank-tabs" style="display: flex; list-style: none; padding: 0; border-bottom: 1px solid #eee; margin-bottom: 15px;">
                    <li class="active" style="padding: 10px 20px; color: #fc824c; border-bottom: 2px solid #fc824c; cursor: pointer; font-weight: bold;">전체</li>
                    <li style="padding: 10px 20px; color: #666; cursor: pointer;">주방/욕실</li>
                    <li style="padding: 10px 20px; color: #666; cursor: pointer;">인테리어</li>
                    <li style="padding: 10px 20px; color: #666; cursor: pointer;">패션/잡화</li>
                </ul>
                <div class="tab_content">
                    <dl style="margin: 0;">
                        @foreach(range(1, 5) as $i)
                        <dd style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #f9f9f9; margin: 0;">
                            <span style="width: 25px; height: 25px; display: inline-block; background: url('{{ asset('images/legacy/icon/r_number_0'.$i.'.png') }}') no-repeat; margin-right: 15px;"></span>
                            <p style="flex: 1; margin: 0; font-size: 14px; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">[베스트 {{$i}}위] 시즌 한정 특가 상품 - 인공지능 배송 서비스 가능 상품</p>
                        </dd>
                        @endforeach
                    </dl>
                </div>
            </div>
            <button style="margin: 20px auto 0; display: block; padding: 10px 30px; border-radius: 20px; border: none; background: #f7f8f9; font-weight: bold; cursor: pointer;">BEST 100 더보기</button>
        </div>

        <div class="bestbnr" style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
            <div style="background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; flex: 1;">
                <img src="https://via.placeholder.com/400x300?text=Ranking+Banner+1" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div style="background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; flex: 1;">
                <img src="https://via.placeholder.com/400x300?text=Ranking+Banner+2" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
    </div>

    <!-- 카테고리 인기상품 섹션 (Category Best Section) -->
    <div class="recommend_item" id="category_nav07" style="margin-top: 50px;">
        <div class="marin-tit"><b>카테고리</b> 인기상품</div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
            <div style="background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 20px; display: flex; gap: 15px;">
                <div style="flex: 1; height: 150px; background: #f9f9f9; border-radius: 8px;">
                    <img src="https://via.placeholder.com/150x150?text=Cat+Item+1" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 2; text-align: left;">
                    <h6 style="margin: 0 0 10px; font-size: 16px; font-weight: bold;">주방 가전 베스트 상품</h6>
                    <p style="color: #666; font-size: 14px;">편리한 주방 생활을 위한 필수 아이템 모음</p>
                    <div style="margin-top: 15px; color: #eb5221; font-weight: 800; font-size: 18px;">25,900원</div>
                </div>
            </div>
            <div style="background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 20px; display: flex; gap: 15px;">
                <div style="flex: 1; height: 150px; background: #f9f9f9; border-radius: 8px;">
                    <img src="https://via.placeholder.com/150x150?text=Cat+Item+2" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 2; text-align: left;">
                    <h6 style="margin: 0 0 10px; font-size: 16px; font-weight: bold;">욕실 인테리어 소품</h6>
                    <p style="color: #666; font-size: 14px;">깔끔하고 세련된 욕실을 완성하는 소품</p>
                    <div style="margin-top: 15px; color: #eb5221; font-weight: 800; font-size: 18px;">15,000원</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 하단 서브 배너 (Bottom Sub Banners) -->
    <div style="width: 100%; max-width: 1200px; margin: 40px auto; display: flex; gap: 15px;">
        <div style="flex: 1; height: 120px; border-radius: 12px; overflow: hidden; background: #eee;">
            <img src="https://via.placeholder.com/600x120?text=Bottom+Banner+1" style="width:100%; height:100%; object-fit: cover;">
        </div>
        <div style="flex: 1; height: 120px; border-radius: 12px; overflow: hidden; background: #eee;">
            <img src="https://via.placeholder.com/600x120?text=Bottom+Banner+2" style="width:100%; height:100%; object-fit: cover;">
        </div>
    </div>

    <!-- 반응형 제품 그리드 (Responsive Product Grid) -->
        <div class="container section">
            <div class="marin-tit">
                <b>BEST</b>
                <p>인기 상품 모음</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 20px 0;">
                @foreach(range(1, 4) as $i)
                    <div style="border: 1px solid #eee; padding: 10px; border-radius: 8px; background: #fff;">
                        <div
                            style="width: 100%; height: 200px; background: #f9f9f9; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">
                            <span style="color: #ccc;">상품 이미지 {{ $i }}</span>
                        </div>
                        <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">레거시 스타일 상품명 {{ $i }}</div>
                        <div style="color: #eb5221; font-weight: bold;">10,000원</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection