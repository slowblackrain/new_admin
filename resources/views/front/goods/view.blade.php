@extends('layouts.front')

@section('content')
    <link rel="stylesheet" type="text/css" href="/css/legacy/view.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/buttons.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/sub_page.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/view_responsive.css">

    {{-- Scripts for legacy compatibility --}}
    <script>
        // 전역 변수 설정 (Legacy JS 호환)
        var gl_goods_price = {{ $priceInfo['price'] ?? 0 }};
        var gl_goods_seq = {{ $product->goods_seq }};
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <div id="goods_view_wrap">
        <div id="info">
            <div id="goods_thumbs" class="clearbox">
                <div class="box" style="width:100%; max-width:580px; margin:0 auto; height:auto;">
                    <div class="slides_container"
                        style="border:1px solid #E2E2E2; margin:auto; width:100%; min-height:300px; text-align:center;">
                        @php
                            $viewImage = $product->images->where('image_type', 'view')->first();
                            $imgSrc = '/images/no_image.gif';
                            if ($viewImage) {
                                $iPath = $viewImage->image;
                                if ($iPath) {
                                    if (Str::startsWith($iPath, 'http')) {
                                        $imgSrc = $iPath;
                                    } elseif (strpos($iPath, 'goods_img') !== false) {
                                        $sfx = substr($iPath, strpos($iPath, 'goods_img') + 9);
                                        $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $sfx;
                                    } elseif (strpos($iPath, '/data/goods/') === 0) {
                                        $imgSrc = "http://dometopia.com" . $iPath;
                                    } else {
                                        $imgSrc = "http://dometopia.com/data/goods/" . $iPath;
                                    }
                                }
                            }
                        @endphp
                        <a href="#" style="width:100%; display:inline-block;">
                            <img src="{{ $imgSrc }}" alt="{{ $product->goods_name }}" style="width:100%; height:auto;"
                                onerror="this.src='/images/no_image.gif'" />
                        </a>
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if($product->images->where('image_type', 'view')->count() > 1)
                    <div class="box_thumbs">
                        <ul class="pagination clearbox">
                            @foreach($product->images->where('image_type', 'view') as $img)
                                @php
                                    $iPath = $img->image;
                                    $tSrc = '/images/no_image.gif';
                                    if ($iPath) {
                                        if (Str::startsWith($iPath, 'http')) {
                                            $tSrc = $iPath;
                                        } elseif (strpos($iPath, 'goods_img') !== false) {
                                            $sfx = substr($iPath, strpos($iPath, 'goods_img') + 9);
                                            $tSrc = "https://dmtusr.vipweb.kr/goods_img" . $sfx;
                                        } elseif (strpos($iPath, '/data/goods/') === 0) {
                                            $tSrc = "http://dometopia.com" . $iPath;
                                        } else {
                                            $tSrc = "http://dometopia.com/data/goods/" . $iPath;
                                        }
                                    }
                                @endphp
                                <li>
                                    <a href="javascript:void(0);"
                                        onclick="changeMainImage('{{ $tSrc }}')">
                                        <img src="{{ $tSrc }}" width="85" height="85"
                                            onerror="this.src='/images/no_image.gif'" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <script>
                    function changeMainImage(src) {
                        const img = document.querySelector('#goods_thumbs .slides_container img');
                        if (img) img.src = src;
                    }
                </script>
            </div>

            {{-- Right Side: Goods Info --}}
            <div class="goods_info clearbox">
                <form name="goodsForm" id="goodsForm" method="post" action="{{ route('cart.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="goods_seq" value="{{ $product->goods_seq }}">

                    <div class="container">
                        <div class="pl_name">
                            <h2>{{ $product->goods_name }}</h2>
                            <div class="pl_icon" onclick='goods_view_wish({{ $product->goods_seq }})'>
                                <p>찜하기</p>
                                <i class="fas fa-heart btn-wish" title="위시리스트 추가"></i>
                            </div>
                            @if($product->summary)
                                <h3>{{ $product->summary }}</h3>
                            @endif
                        </div>

                        {{-- Price Info Table --}}
                        <table class="goods_spec_table" width="100%">
                            <tr style="width:15%; border-top: 2px solid #000;">
                                <th style="width:15%;">모델명</th>
                                <td style="width:22%;"><span class="goods_code">{{ $product->goods_scode }}</span></td>
                                <td colspan="2" style="width:15%; text-align:center;">
                                    @if($product->tax != 'exempt') VAT별도 @else 면세 @endif
                                </td>
                                <th style="width:16%;">관리코드</th>
                                <td style="width:22%;"><span class="goods_code">{{ $product->goods_code }}</span></td>
                            </tr>
                            <tr>
                                {{-- Complex Pricing Logic matching view.html --}}
                                @php
                                    $scode = $product->goods_scode;
                                    $is_GKD = substr($scode, 0, 3) == 'GKD';
                                    $is_GTQ_GKQ = (substr($scode, 0, 3) == 'GTQ' || substr($scode, 0, 3) == 'GKQ') && $scode != 'GTQ59308';
                                    $is_P = substr($scode, 2, 1) == 'P';
                                    $is_ATQ = substr($scode, 0, 3) == 'ATQ';
                                    $mtype_discount = $priceInfo['mtype_discount'] ?? 0;
                                    $ori_price = $priceInfo['ori_price'] ?? 0;
                                    $sale_price = $priceInfo['price'] ?? 0;
                                    $hundred_ea = $priceInfo['hundred_ea'] ?? 0;
                                    $hundred_price = $priceInfo['hundred_price'] ?? 0;
                                    $fifty_ea = $priceInfo['fifty_ea'] ?? 0;
                                    $fifty_price = $priceInfo['fifty_price'] ?? 0;
                                @endphp

                                @if($mtype_discount == 0 && ($ori_price == $hundred_price && $ori_price == $fifty_price))
                                    <th style="width:15%;">도매가</th>
                                    <td colspan="6" style="box-shadow: 1px -2px 21px #e0e2e4;">
                                        <span class="goods_code">{{ number_format($ori_price) }} 원</span>
                                    </td>
                                @else
                                    @if(($is_P || $is_ATQ || $is_GTQ_GKQ) && $scode != 'GTQ59308')
                                        <th style="width:15%;">공급가</th>
                                    @endif

                                    <td colspan="6"
                                        style="padding:0px; border-top: 2px solid #e9ecef; box-shadow: 1px -2px 21px #e0e2e4;">
                                        {{-- Case GKD --}}
                                        @if($is_GKD)
                                            @if($hundred_ea > 0 && $fifty_ea > 0)
                                                <div class="goods_spec_table">
                                                    <table style="width:100%; border-spacing:0; border-collapse:collapse;">
                                                        <colgroup>
                                                            <col width="30%" />
                                                            <col width="30%" />
                                                            <col width="20%" />
                                                            <col width="20%" />
                                                        </colgroup>
                                                        <thead>
                                                            <tr style="background:#f9f9f9; text-align:center;">
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <span class="price_blue">공장도가<br>({{ number_format($hundred_ea) }}개
                                                                        이상)</span>
                                                                </th>
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    도매할인가<br>({{ number_format($fifty_ea) }}개 이상)
                                                                </th>
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    도매가</th>
                                                                <th style="padding:10px; border-bottom:1px solid #eee;">소매가</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr style="text-align:center;">
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <span class="price_red">{{ number_format($hundred_price) }} 원</span>
                                                                </td>
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    {{ number_format($fifty_price) }} 원
                                                                </td>
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <span
                                                                        class="price_red">{{ number_format($ori_price - $mtype_discount) }}
                                                                        원</span>
                                                                </td>
                                                                <td style="padding:10px; border-bottom:1px solid #eee;">
                                                                    {{ number_format($ori_price) }} 원
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <!-- Price Info Table -->
                                                <div class="goods_spec_table">
                                                    <table style="width:100%; border-spacing:0; border-collapse:collapse;">
                                                        <colgroup>
                                                            <col width="30%" />
                                                            <col width="30%" />
                                                            <col width="20%" />
                                                            <col width="20%" />
                                                        </colgroup>
                                                        <thead>
                                                            <tr style="background:#f9f9f9; text-align:center;">
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    수입가</th>
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    도매할인가</th>
                                                                <th
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    도매가</th>
                                                                <th style="padding:10px; border-bottom:1px solid #eee;">소매가</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr style="text-align:center;">
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <span
                                                                        class="price_red">{{ number_format($priceInfo['suip_price']) }}
                                                                        원</span>
                                                                </td>
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <span
                                                                        class="price_blue">{{ number_format($priceInfo['domae_discount']) }}
                                                                        원</span>
                                                                </td>
                                                                <td
                                                                    style="padding:10px; border-bottom:1px solid #eee; border-right:1px solid #eee;">
                                                                    <b>{{ number_format($priceInfo['domae_price']) }} 원</b>
                                                                </td>
                                                                <td style="padding:10px; border-bottom:1px solid #eee;">
                                                                    <strike>{{ number_format($priceInfo['somae_price']) }} 원</strike>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            {{-- Case GTQ/GKQ --}}
                                        @elseif($is_GTQ_GKQ)
                                            <div class="goods_gtp">
                                                <font><span><b class="goods_gtp_s">노마진 특가</b>
                                                        <p>{{ number_format($ori_price) }}</p> 원
                                                    </span></font>
                                            </div>

                                            {{-- Case P --}}
                                        @elseif($is_P)
                                            <div class="goods_gtp">
                                                <font><del>소매가 {{ number_format(floor($ori_price * 2.2 / 10) * 10) }}원</del> <i
                                                        class="fas fa-chevron-right"></i>
                                                    <span>땡처리가 <p>{{ number_format($ori_price) }}</p> 원</span>
                                                </font>
                                            </div>

                                            {{-- Case ATQ --}}
                                        @elseif($is_ATQ)
                                            <div class="goods_gtp">
                                                <font><span><b class="goods_gtp_s">수입특가</b>
                                                        <p>{{ number_format($ori_price) }}</p> 원
                                                    </span></font>
                                            </div>

                                            {{-- Default Case (Catch-all) --}}
                                        @else
                                            <div class="goods_spec_table">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <colgroup>
                                                        <col width="30%" />
                                                        <col width="30%" />
                                                        <col width="20%" />
                                                        <col width="20%" />
                                                    </colgroup>
                                                    <thead>
                                                        <tr>
                                                            <th class="gst_th">
                                                                @if(substr($scode, 0, 2) == 'GK') 공장도가 @else 수입가 @endif
                                                                @if($hundred_ea > 0)<br>({{ number_format($hundred_ea) }}개 이상)@endif
                                                            </th>
                                                            <th class="gst_th">
                                                                도매할인가
                                                                @if($fifty_ea > 0)<br>({{ number_format($fifty_ea) }}개 이상)@endif
                                                            </th>
                                                            <th class="gst_th">
                                                                <span class="price_blue">도매가</span>
                                                            </th>
                                                            <th class="gst_th">소매가</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="gst_td">
                                                                @if($hundred_ea > 0) {{ number_format($hundred_price) }} 원 @else
                                                                {{ number_format($sale_price) }} 원 @endif
                                                            </td>
                                                            <td class="gst_td">
                                                                @if($fifty_ea > 0) {{ number_format($fifty_price) }} 원 @else
                                                                {{ number_format($sale_price) }} 원 @endif
                                                            </td>
                                                            <td class="gst_td">
                                                                <span
                                                                    class="price_red">{{ number_format($ori_price - $mtype_discount) }}
                                                                    원</span>
                                                            </td>
                                                            <td class="gst_td">
                                                                {{ number_format($ori_price) }} 원
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            {{-- Shipping Info Row --}}
                            <tr>
                                <th class="gst_th">배송비</th>
                                <td colspan="6" class="gst_td">
                                    @if($shippingInfo['is_free'])
                                        <span class="price_blue" style="font-weight:bold;">무료배송</span>
                                        (주문금액 {{ number_format($shippingInfo['threshold']) }}원 이상)
                                    @else
                                        <span style="font-weight:bold;">{{ number_format($shippingInfo['base_cost']) }}원</span>
                                        <span
                                            style="color:#888; font-size:12px;">({{ number_format($shippingInfo['threshold']) }}원
                                            이상 구매 시 무료)</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        
                        {{-- Corporate Member Promo (Legacy Parity) --}}
                        @if(!session('gubun'))
                        <div class="option-box2" style="margin-top:10px; border-top:1px solid #ddd; padding:10px 0;">
                            <a href="/page/index?tpl=etc/business_info.html" target="_blank">
                                <img src="/images/legacy/asset/wholesale6.jpg" alt="기업회원 우대 정책">
                            </a>
                            <br>
                            <span style="font-size:12px; color:#666;">
                                ※기업(판매, 구매) 회원가입시 수량과 관계없이 <span style="color:red; font-weight:bold;">도매가</span>로 구매할 수 있습니다.
                            </span>
                            <a href="/member/agreement" target="_blank" style="vertical-align:middle;">
                                <img src="/images/legacy/asset/agreement6.jpg" alt="회원가입">
                            </a>
                        </div>
                        @endif

                        {{-- Options Area --}}
                        <div id="select_option_lay">
                            {{-- Specific Logic for Printing Options (SubOptions) --}}
                            @php
                                $printingSubOptions = $product->subOptions->where('suboption_title', '인쇄');
                            @endphp

                            @if($printingSubOptions->count() > 0)
                                <h3 class="mt20"
                                    style="font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
                                    인쇄옵션
                                    <button type="button" class="doto-goodsOpt-folding" onclick="toggleOptionTable(this)"
                                        style="width:31px; height:31px; border:none; cursor:pointer; background: url('/images/legacy/icon/cl_m.jpg') no-repeat; vertical-align:middle; margin-left:12px;"></button>
                                    
                                    <button type="button" class="button bgblue" style="color:white;padding:5px; margin-left:5px;" onclick="location.href='/etc/print_info'">인쇄비용안내</button>
                                    <button type="button" class="button" style="color: red;padding:5px;background: yellow;font-weight: bold;font-size: 13px; margin-left:5px;" >50만원 이상 구매시 1도인쇄/몰드 비용 무료입니다.</button>
                                </h3>
                                <div class="goods_option_table" id="goods_option_input_area"
                                    style="display:block; border: 1px solid #e9ecef; border-bottom: none;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <colgroup>
                                            <col width="100" />
                                            <col />
                                        </colgroup>
                                        <tr style="height:40px; border-bottom:1px solid #e9ecef;">
                                            <th class="inputsTitle"
                                                style="background:#FFF; padding-left:10px; text-align:left; font-weight:normal; color:#333;">
                                                인쇄
                                            </th>
                                            <td style="padding:0 10px;" colspan="2">
                                                <select id="suboption_select_box" onchange="addSubOption()"
                                                    style="width:100%; height:25px; border:1px solid #ddd;">
                                                    <option value="">선택</option>
                                                    @foreach($printingSubOptions as $subOpt)
                                                        @php
                                                            $displayText = $subOpt->suboption;
                                                            switch($subOpt->suboption){
                                                                case "중국1도" :
                                                                    $displayText .= " 50만원 미만시 인쇄비 80원, 몰드 2만원";
                                                                    break;
                                                                case "중국2도" :
                                                                    $displayText .= " 150원/몰드4만원/500개당 몰드1개 무료";
                                                                    break;
                                                                case "중국3도" :
                                                                    $displayText .= " 200원/몰드6만원/500개당 몰드1개 무료";
                                                                    break;
                                                                case "중국4도" :
                                                                    $displayText .= " 250원/몰드8만원/500개당 몰드1개 무료";
                                                                    break;
                                                                case "한국1도" :
                                                                    $displayText .= " 상담후 결제";
                                                                    break;
                                                                case "중국스티커" :
                                                                    $displayText .= " 기본 1,000장 15,000원/작업비장당80원별도/납기12일내외 ";
                                                                    break;
                                                            }
                                                        @endphp
                                                        <option value="{{ $subOpt->suboption_seq }}"
                                                            data-price="{{ $subOpt->price }}"
                                                            data-name="{{ $displayText }}">
                                                            {{ $displayText }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        {{-- Render Printing Text Input --}}
                                        @foreach($product->inputs as $input)
                                            @if($input->input_name == '인쇄문구')
                                                <tr style="height:40px; border-bottom:1px solid #e9ecef;">
                                                    <th class="inputsTitle"
                                                        style="background:#FFF; padding-left:10px; text-align:left; font-weight:normal; color:#333;">
                                                        {{ $input->input_name }}
                                                    </th>
                                                    <td style="padding:0 10px;" colspan="2">
                                                        <input type="text" name="inputs[{{ $input->input_seq }}]" class="input_text"
                                                            style="width:100%; height:25px; border:1px solid #ddd;">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        {{-- Render Printing Image Inputs (Multi-file) --}}
                                        @php $fileInputCount = 0; @endphp
                                        @foreach($product->inputs as $input)
                                            @if($input->input_name == '인쇄이미지')
                                                @php $fileInputCount++; @endphp
                                                <tr class="printing-image-row" id="printing_image_row_{{ $fileInputCount }}" 
                                                    style="height:40px; border-bottom:1px solid #e9ecef; @if($fileInputCount > 1) display:none; @endif">
                                                    <th class="inputsTitle"
                                                        style="background:#FFF; padding-left:10px; text-align:left; font-weight:normal; color:#333;">
                                                        {{ $input->input_name }}
                                                        @if($fileInputCount == 1)
                                                            <button type="button" class="button bgblue" style="width:20px; height:20px; line-height:18px; padding:0; text-align:center;" onclick="addFileRow()">+</button>
                                                            <button type="button" class="button bgblue" style="width:20px; height:20px; line-height:18px; padding:0; text-align:center;" onclick="removeFileRow()">-</button>
                                                        @endif
                                                    </th>
                                                    <td style="padding:0 10px;" colspan="2">
                                                        <input type="file" name="inputs[{{ $input->input_seq }}]" class="input_file">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        {{-- Purchase Amount Row --}}
                                        <tr class="quanity_row" style="height:40px; border-bottom:1px solid #e9ecef; background-color:#f7f8f9;">
                                            <td class="option_text quantity_cell_sub" style="padding-left:10px;"> 구매금액</td>
                                            <td class="quantity_cell_sub" align="center">
                                                <span class="first_label">1</span>개 X <span class="first_price">{{ number_format($product->price) }}</span>원
                                            </td>
                                            <td class="quantity_cell_sub_price" align="right" style="padding-right:10px;">
                                                <strong class="first_totprice" style="font-size:14px; color:#d32f2f;">{{ number_format($product->price) }}</strong>원
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Options Select --}}
                        @php
                            $options = $product->option;
                            $hasOptions = $options->count() > 1 || ($options->count() === 1 && trim($options->first()->option1) !== '');
                            $defaultOption = $options->first();
                            $defaultSeq = $defaultOption ? $defaultOption->option_seq : '';
                        @endphp

                        @if($hasOptions)
                            <div class="option_area" style="margin-top:20px;">
                                <select class="option_select" id="option_select_box" onchange="addOption()"
                                    style="width:100%; padding:8px; border:1px solid #ddd;">
                                    <option value="">옵션 선택 (필수)</option>
                                    @foreach($options as $opt)
                                        <option value="{{ $opt->option_seq }}" data-price="{{ $opt->price }}"
                                            data-name="{{ $opt->option1 }}" data-seq="{{ $opt->option_seq }}">
                                            {{ $opt->option1 }}
                                            @if($opt->price > 0) ({{ number_format($opt->price) }}원) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- No Options: Quantity Input directly --}}
                            <div class="default_qty_area"
                                style="padding: 15px 0; border-bottom: 1px solid #eee; margin-top:20px;">
                                <span style="font-weight:bold; margin-right:10px;">수량</span>
                                <input type="number" id="default_qty" value="1" min="1"
                                    style="width: 60px; text-align: center; padding: 5px; border: 1px solid #ddd;"
                                    onchange="updateTotal()" onkeyup="updateTotal()" onclick="updateTotal()">
                            </div>
                        @endif

                        {{-- Selected Options Container --}}
                        <div id="selected_options_container"
                            style="margin-top: 10px; background: #f8f9fa; border-top: 1px solid #ddd;">
                            <table class="goods_quantity_table" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tbody id="selected_options_tbody"></tbody>
                            </table>
                        </div>
                        <div id="form_hidden_inputs"></div>


                        {{-- Total Price & Buttons (Legacy Style) --}}
                        <div class="total price" style="width:100%; text-align:right; margin-top:20px;">
                            <span class="total_goods_price_txt" id="total_price"
                                style="font-size:30px; color:#d32f2f; font-weight:bold;">0원</span>
                            <div style="margin-top:20px;">
                                <button type="button" class="button bgred" onclick="processOrder()">바로구매</button>
                                <button type="button" class="button bgblue" onclick="processCart()">장바구니</button>
                            </div>
                        </div>

                    </div> {{-- End container --}}
                </form>
            </div> {{-- End goods_info --}}
        </div> {{-- End info --}}

        {{-- Detail Tabs --}}
        <div id="detail" class="boxwrap">
            <div class="detail tab-nav">
                <a href="javascript:void(0);" class="item on" onclick="switchTab('detail', this)">상품상세정보</a>
                <a href="javascript:void(0);" class="item" onclick="switchTab('guide', this)">상품구매 필독사항</a>
                <a href="javascript:void(0);" class="item" onclick="switchTab('shipping', this)">배송/거래정보 안내</a>
                <a href="javascript:void(0);" class="item" onclick="switchTab('review', this)">상품사용후기</a>
                <a href="javascript:void(0);" class="item" onclick="switchTab('qna', this)">상품Q&A</a>
            </div>

            <div id="tab_detail" class="tab_content_area active">
                @if(isset($contentInfo) && count(array_filter($contentInfo)) > 0)
                    <div class="table-01">
                        <table>
                            @if(!empty($contentInfo['usage']))
                                <tr>
                                    <th>상품용도 및 특징</th>
                                    <td>{{ $contentInfo['usage'] }}</td>
                                </tr>
                            @endif
                            @if(!empty($makerName))
                                <tr>
                                    <th>제조자/수입자</th>
                                    <td>{{ $makerName }}</td>
                                </tr>
                            @endif
                            @if(!empty($contentInfo['material']))
                                <tr>
                                    <th>상품재질</th>
                                    <td>{{ $contentInfo['material'] }}</td>
                                </tr>
                            @endif
                            @if(!empty($contentInfo['size']))
                                <tr>
                                    <th>사이즈</th>
                                    <td>{{ $contentInfo['size'] }}</td>
                                </tr>
                            @endif
                            @if(!empty($contentInfo['color']))
                                <tr>
                                    <th>색상종류</th>
                                    <td>{{ $contentInfo['color'] }}</td>
                                </tr>
                            @endif
                            @if(!empty($contentInfo['delivery_period']))
                                <tr>
                                    <th>배송기일</th>
                                    <td>{{ $contentInfo['delivery_period'] }}일 내외</td>
                                </tr>
                            @endif
                            @if(!empty($contentInfo['weight']))
                                <tr>
                                    <th>무게(포장포함)</th>
                                    <td>{{ $contentInfo['weight'] }}</td>
                                </tr>
                            @endif
                            @if(!empty($product->origin))
                                <tr>
                                    <th>원산지</th>
                                    <td>{{ $product->origin }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>A/S문의</th>
                                <td>032-575-3477</td>
                            </tr>
                            <tr>
                                <th>모델명</th>
                                <td>{{ $product->goods_name }}</td>
                            </tr>
                            <tr>
                                <th>관리코드</th>
                                <td>{{ $product->goods_code }}</td>
                            </tr>
                        </table>
                    </div>
                @endif
                <div class="detail-img">
                    @if(isset($allTopImg)) {!! $allTopImg !!} @endif
                    @if(isset($gtdImg)) {!! $gtdImg !!} @endif
                    @if(isset($gusImg)) {!! $gusImg !!} @endif

                    @if(isset($detailImgMap)) {!! $detailImgMap !!} @endif

                    {!! $product->common_contents !!}
                    {!! $product->contents !!}
                </div>
            </div>

            <div id="tab_guide" class="tab_content_area" style="display:none;">
                <div class="guide_section" style="padding:20px;">
                    <h4>1. 모델명 분류에 따른 배송기간, 구매 단위 안내</h4>
                    <table class="sub-table2">
                        <tr>
                            <th>① &nbsp;&nbsp;GT</th>
                            <td>:</td>
                            <td>낱개 구매와 당일 발송이 가능한 "<span style="color:red;">수입</span>" 상품입니다.</td>
                        </tr>
                        <tr>
                            <th>② &nbsp;&nbsp;GK</th>
                            <td>:</td>
                            <td>낱개 구매와 당일 발송이 가능한 "<span style="color:red;">국내</span>" 상품입니다.</td>
                        </tr>
                        <tr>
                            <th>③ &nbsp;&nbsp;XT</th>
                            <td>:</td>
                            <td>낱개 구매와 당일 발송이 가능한 "<span style="color:red;">크리스마스</span>" 상품입니다.</td>
                        </tr>
                        <tr>
                            <th>④ GKM</th>
                            <td>:</td>
                            <td>산지에서 직접 발송되는 "<span style="color:red;">농ㆍ수ㆍ축산물</span>" 상품입니다.</td>
                        </tr>
                        <tr>
                            <th>⑤ GDR</th>
                            <td>:</td>
                            <td>개인통관 부호를 통해 수입하는 "<span style="color:red;">해외 직구</span>" 상품입니다. (15일 내외)</td>
                        </tr>
                        <tr>
                            <th>⑥ GDF</th>
                            <td>:</td>
                            <td>"<span style="color:red;">중국 제조사</span>"에서 출고되며 낱개 구매가 되는 상품입니다. (10일 내외)</td>
                        </tr>
                        <tr>
                            <th>⑦ GKD</th>
                            <td>:</td>
                            <td>"<span style="color:red;">국내 제조사</span>"에서 출고되며 낱개 구매가 되는 상품입니다. (10일 내외)</td>
                        </tr>
                        <tr>
                            <th>⑧ ATS</th>
                            <td>:</td>
                            <td>"<span style="color:red;">중국 제조사</span>"에서 출고되며 설정된 구매 단위부터 구매가 가능합니다. (10일 내외)</td>
                        </tr>
                        <tr>
                            <th>⑨ AKS</th>
                            <td>:</td>
                            <td>"<span style="color:red;">국내 제조사</span>"에서 출고되며 설정된 구매 단위부터 구매가 가능합니다. (5일 내외)</td>
                        </tr>
                        <tr>
                            <th>※ 공통 </th>
                            <td>:</td>
                            <td>제조사에서 직접 출고되는 상품은 변심 반품이 불가합니다.<br />환율 변동과 제조사의 상황에 따라 가격과 납품 일정이 변동될 수 있습니다.</td>
                        </tr>
                    </table>
                    <br>
                    <h4>2. 사업자 정회원 특전</h4>
                    <ul class="guide_list">
                        <li>① 사업자라면 누구나 정회원에 가입할 수 있습니다.</li>
                        <li>② 모든 상품을 수량과 관계없이 도매가로 구매할 수 있습니다.</li>
                        <li>③ 대량 구매 시 별도 할인 혜택을 제공합니다.</li>
                        <li>④ 15만원 이상 구매 시 무료배송 혜택을 받습니다.</li>
                        <li>⑤ 재고를 보유하며 판매하는 셀러에게는 반품 기간 3개월을 제공합니다.</li>
                        <li>⑥ 누적 판매액에 따른 판매 장려금을 받을 수 있습니다.</li>
                    </ul>
                </div>
            </div>

            <div id="tab_shipping" class="tab_content_area" style="display:none;">
                <div class="guide_section" style="padding:20px;">
                    <h4>배송안내</h4>
                    <ul class="guide_list">
                        <li>① 배송지역: 전국</li>
                        <li>② 배송기간: 2일 이상</li>
                        <li>③ 배송방법: 한진택배</li>
                        <li>④ 배송비: 박스당 2,500원 (제주/도서산간 추가비용 발생)</li>
                    </ul>
                    <br>
                    <h4>거래정보</h4>
                    <ul class="guide_list">
                        <li>① 상품 배송 기간: 결제 후 2일 이상(영업일 기준)</li>
                        <li>② 소비자의 단순 변심, 착오 구매에 따른 반품 안내
                            <p style="padding-left:15px; margin:5px 0; color:#666;">
                                - 상품 수령 후 7일 이내 반품 가능<br>
                                - 변심의 경우 왕복 배송비 부담<br>
                                - 상품 사용 및 포장지 훼손 시 반품 불가
                            </p>
                        </li>
                        <li>③ 상품의 교환, 반품, 보증 조건 및 품질 보증 기준
                            <p style="padding-left:15px; margin:5px 0; color:#666;">
                                - 교환, 반품, 보증은 '소비자 기본법에 따른 소비자 분쟁 해결' 관계 법규 참조
                            </p>
                        </li>
                        <li>④ 소비자 피해 보상의 처리 및 소비자와 사업자 사이의 분쟁 처리 사항
                            <p style="padding-left:15px; margin:5px 0; color:#666;">
                                - 소비자 기본법에 따른 소비자 분쟁 해결 기준 등 관계 법규 참조
                            </p>
                        </li>
                        <li>⑤ 거래에 관한 약관 또는 공지 안내: 상세페이지 내용 및 하단의 이용약관 참조.</li>
                        <li>⑥ (주)트리(도매토피아)는 해외직구 상품의 통신판매중개자로서 통신판매의 당사자가 아닙니다. 상품, 정보, 거래의 책임은 트리월드(주)에 있습니다.</li>
                    </ul>
                </div>
            </div>

            <div id="tab_review" class="tab_content_area" style="display:none; padding:20px;">
                <div id="review_list_container">로딩중...</div>
            </div>

            <div id="tab_qna" class="tab_content_area" style="display:none; padding:20px;">
                <div id="qna_list_container">로딩중...</div>
            </div>
        </div>
    </div>

    {{-- Quick View Floating Container (Legacy Style) --}}
    <div id="view_quick_container"
        style="display:none; position:fixed; top:80px; right:50%; margin-right:-600px; width:280px; background:#fff; border:2px solid #555; z-index:999; padding:15px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
        <div style="font-weight:bold; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:10px;">
            {{ $product->goods_name }}
        </div>

        <!-- Display Selected Options Summary in Quick View -->
        <div id="quick_selected_dev" style="max-height:100px; overflow-y:auto; font-size:12px; margin-bottom:10px;">
            <!-- Synced via JS -->
        </div>

        <div style="text-align:right; margin-bottom:10px;">
            <span style="font-weight:bold;">총 구매금액</span>
            <span id="quick_total_price" style="font-size:20px; color:#d32f2f; font-weight:bold; display:block;">0원</span>
        </div>

        <div style="display:flex; justify-content:space-between;">
            <button type="button" class="button bgred" onclick="processOrder()"
                style="width:48%; height:40px;">바로구매</button>
            <button type="button" class="button bgblue" onclick="processCart()"
                style="width:48%; height:40px;">장바구니</button>
        </div>
    </div>

    {{-- Custom Cart Confirmation Modal --}}
    <div id="cart_confirm_modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:30px; border-radius:10px; text-align:center; width:300px; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
            <p style="font-size:16px; margin-bottom:20px; font-weight:bold;">장바구니에 담겼습니다.</p>
            <div style="display:flex; justify-content:space-between; gap:10px;">
                <button type="button" onclick="closeCartModal()" style="flex:1; padding:10px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:5px;">쇼핑 계속하기</button>
                <button type="button" onclick="location.href='{{ route('cart.index') }}'" style="flex:1; padding:10px; border:none; background:#3ba0ff; color:#fff; cursor:pointer; border-radius:5px;">장바구니 이동</button>
            </div>
        </div>
    </div>

    {{-- JS Logic --}}
    <script>
        const priceInfo = @json($priceInfo ?? []);
        const hasOptions = @json($hasOptions);
        const defaultSeq = @json($defaultSeq);
        const hundredEa = priceInfo.hundred_ea || 0;
        const fiftyEa = priceInfo.fifty_ea || 0;

        // Discount Amounts (Per Unit)
        const discountMtype = {{ $product->mtype_discount ?? 0 }};
        const discount50 = {{ $product->fifty_discount ?? 0 }};
        const discount100 = {{ $product->hundred_discount ?? 0 }};

        let selectedOptions = {};
        let selectedSubOptions = {};

        document.addEventListener('DOMContentLoaded', function () {
            updateTotal();
            initStickySidebar();

            // Scroll event for Quick View
            window.addEventListener('scroll', function () {
                const infoDiv = document.getElementById('info');
                const quickDiv = document.getElementById('view_quick_container');
                const infoRect = infoDiv.getBoundingClientRect();

                // Show floating menu when the main info section is scrolled past
                if (infoRect.bottom < 100) {
                    quickDiv.style.display = 'block';
                } else {
                    quickDiv.style.display = 'none';
                }
            });
        });

        // Sticky Sidebar Logic (Original - kept for reference or removal if fully replaced)
        function initStickySidebar() {
            // Legacy site actually uses the floating quick container more prominently.
            // keeping this minimal.
        }

        function switchTab(tabName, element) {
            // Hide all tabs
            document.querySelectorAll('.tab_content_area').forEach(el => el.style.display = 'none');
            // Show target tab
            document.getElementById('tab_' + tabName).style.display = 'block';

            // Toggle active state
            document.querySelectorAll('.detail.tab-nav .item').forEach(el => el.classList.remove('on'));
            if (element) element.classList.add('on');

            // Load Board Content if reviewing/qna
            if (tabName === 'review') {
                loadBoardList('goods_review', gl_goods_seq, 'review_list_container');
            } else if (tabName === 'qna') {
                loadBoardList('goods_qna', gl_goods_seq, 'qna_list_container');
            }
        }

        function loadBoardList(boardId, goodsSeq, targetId) {
            const container = document.getElementById(targetId);
            container.innerHTML = '<div style="text-align:center; padding:20px;">로딩중...</div>';
            
            fetch(`/board/goods-list?id=${boardId}&goods_seq=${goodsSeq}`)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading board:', error);
                    container.innerHTML = '<div style="text-align:center; color:red;">불러오기 실패</div>';
                });
        }

        function toggleOptionTable(btn) {
            const area = document.getElementById('goods_option_input_area');
            if (area.style.display === 'none') {
                area.style.display = 'block';
                if (btn) btn.classList.remove('on');
            } else {
                area.style.display = 'none';
                if (btn) btn.classList.add('on');
            }
        }

        function addOption() {
            const selectBox = document.getElementById('option_select_box');
            if (!selectBox) return;
            const selectedIdx = selectBox.selectedIndex;
            if (selectedIdx === 0) return;

            const option = selectBox.options[selectedIdx];
            const seq = option.getAttribute('data-seq');
            const name = option.getAttribute('data-name');
            const price = parseFloat(option.getAttribute('data-price')) || 0;

            if (selectedOptions[seq]) {
                alert('이미 선택된 옵션입니다.');
                selectBox.selectedIndex = 0;
                return;
            }

            selectedOptions[seq] = { name: name, price: price, qty: 1 };
            renderOptionRow(seq, 'option');
            updateTotal();
            selectBox.selectedIndex = 0;
        }

        function addSubOption() {
            const selectBox = document.getElementById('suboption_select_box');
            if (!selectBox) return;
            const selectedIdx = selectBox.selectedIndex;
            if (selectedIdx === 0) return;

            const option = selectBox.options[selectedIdx];
            const seq = option.value;
            const name = option.getAttribute('data-name');
            const price = parseFloat(option.getAttribute('data-price')) || 0;

            if (selectedSubOptions[seq]) {
                alert('이미 선택된 옵션입니다.');
                selectBox.selectedIndex = 0;
                return;
            }

            selectedSubOptions[seq] = { name: name, price: price, qty: 1 }; // Suboptions usually default to 1 qty, synced with main option? Or independent? Legacy implies independent or fixed.
            renderOptionRow(seq, 'suboption');
            updateTotal();
            selectBox.selectedIndex = 0;
        }

        function renderOptionRow(seq, type) {
            const containerId = 'selected_options_tbody';
            const tbody = document.getElementById(containerId);
            const row = document.createElement('tr');
            row.id = `${type}_row_${seq}`;
            row.style.borderBottom = '1px solid #eee';
            
            let item;
            if (type === 'option') item = selectedOptions[seq];
            else item = selectedSubOptions[seq];

            const namePrefix = type === 'suboption' ? '[추가] ' : '';

            row.innerHTML = `
                <td style="padding: 10px; text-align: left;"><span style="font-weight: bold;">${namePrefix}${item.name}</span></td>
                <td style="padding: 10px; text-align: center;">
                    <div style="display: inline-flex; align-items: center; border: 1px solid #ddd;">
                        <button type="button" onclick="changeQty('${seq}', -1, '${type}')" style="width: 25px; height: 25px;">-</button>
                        <input type="text" value="${item.qty}" readonly style="width: 40px; text-align: center; border: none;">
                        <button type="button" onclick="changeQty('${seq}', 1, '${type}')" style="width: 25px; height: 25px;">+</button>
                    </div>
                </td>
                <td style="padding: 10px; text-align: right;">
                    <span style="font-weight: bold;">${new Intl.NumberFormat('ko-KR').format(item.price * item.qty)}원</span>
                    <button type="button" onclick="removeOption('${seq}', '${type}')" style="margin-left:5px;">x</button>
                </td>
            `;
            tbody.appendChild(row);
        }

        function changeQty(seq, delta, type) {
            let item;
            if (type === 'suboption') item = selectedSubOptions[seq];
            else if (type === 'option') item = selectedOptions[seq];
            else {
                // Legacy support for single qty inputs if any
                return; 
            }

            if (!item) return;

            let newQty = item.qty + delta;
            if (newQty < 1) newQty = 1;
            item.qty = newQty;
            
            const row = document.getElementById(`${type}_row_${seq}`);
            if(row) {
                row.querySelector('input').value = newQty;
                row.querySelector('td:last-child span').innerText = new Intl.NumberFormat('ko-KR').format(item.price * newQty) + '원';
            }

            updateTotal();
        }

        function removeOption(seq, type) {
            if (type === 'suboption') {
                delete selectedSubOptions[seq];
            } else {
                delete selectedOptions[seq];
            }
            const row = document.getElementById(`${type}_row_${seq}`);
            if(row) row.remove();
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            const hiddenContainer = document.getElementById('form_hidden_inputs');
            hiddenContainer.innerHTML = '';

            // Sync to Quick View
            const quickDev = document.getElementById('quick_selected_dev');
            quickDev.innerHTML = '';

            // Calculate Suboptions Total First (to add to EACH main option? No, usually separate lines or added to total)
            // In legacy, suboptions are seemingly independent items in the cart or attached to the main item.
            // For simplicity in calculation, we sum them up. 
            // However, visually they are usually shown.
            
            let subOptionsTotal = 0;
            for (const [seq, item] of Object.entries(selectedSubOptions)) {
                subOptionsTotal += (item.price * item.qty);
                hiddenContainer.innerHTML += `<input type="hidden" name="suboption_seq[]" value="${seq}"><input type="hidden" name="suboption_ea[]" value="${item.qty}">`;
                
                quickDev.innerHTML += `
                    <div class="quick-opt-row">
                        <span style="width:40%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">[추가] ${item.name}</span>
                        <div class="quick-qty-ctrl">
                            <button type="button" onclick="changeQty('${seq}', -1, 'suboption')">-</button>
                            <input type="text" value="${item.qty}" readonly>
                            <button type="button" onclick="changeQty('${seq}', 1, 'suboption')">+</button>
                        </div>
                        <span style="font-size:11px;">${new Intl.NumberFormat('ko-KR').format(item.price * item.qty)}</span>
                        <button type="button" onclick="removeOption('${seq}', 'suboption')" style="border:none; bg:none; cursor:pointer;">x</button>
                    </div>`;
            }


            if (hasOptions) {
                // Render Options
                for (const [seq, item] of Object.entries(selectedOptions)) {
                    let basePrice = item.price;
                    let finalUnitPrice = basePrice;
                    let qty = item.qty;

                    if (hundredEa > 0 && qty >= hundredEa) {
                        finalUnitPrice = basePrice - discount100;
                    } else if (fiftyEa > 0 && qty >= fiftyEa) {
                        finalUnitPrice = basePrice - discount50;
                    } else {
                        finalUnitPrice = basePrice - discountMtype;
                    }

                    total += finalUnitPrice * qty;
                    hiddenContainer.innerHTML += `<input type="hidden" name="option_seq[]" value="${seq}"><input type="hidden" name="ea[]" value="${qty}">`;

                    // Add Interactive Row to Quick View
                    quickDev.innerHTML += `
                        <div class="quick-opt-row">
                            <span style="width:40%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${item.name}</span>
                            <div class="quick-qty-ctrl">
                                <button type="button" onclick="changeQty('${seq}', -1, 'option')">-</button>
                                <input type="text" value="${item.qty}" readonly>
                                <button type="button" onclick="changeQty('${seq}', 1, 'option')">+</button>
                            </div>
                            <span style="font-size:11px;">${new Intl.NumberFormat('ko-KR').format(finalUnitPrice * qty)}</span>
                            <button type="button" onclick="removeOption('${seq}', 'option')" style="border:none; bg:none; cursor:pointer;">x</button>
                        </div>`;
                }
            } else {
                const qtyInput = document.getElementById('default_qty');
                let qty = qtyInput ? parseInt(qtyInput.value) : 1;

                let basePrice = priceInfo.ori_price || 0;
                let finalUnitPrice = basePrice;

                if (hundredEa > 0 && qty >= hundredEa) {
                    finalUnitPrice = basePrice - discount100;
                } else if (fiftyEa > 0 && qty >= fiftyEa) {
                    finalUnitPrice = basePrice - discount50;
                } else {
                    finalUnitPrice = basePrice - discountMtype;
                }

                total = finalUnitPrice * qty;
                hiddenContainer.innerHTML += `<input type="hidden" name="option_seq[]" value="${defaultSeq}"><input type="hidden" name="ea[]" value="${qty}">`;

                // Sync for no-option:
                quickDev.innerHTML += `
                    <div class="quick-opt-row">
                        <span>수량</span>
                        <div class="quick-qty-ctrl">
                            <button type="button" onclick="changeDefaultQty(-1)">-</button>
                            <input type="text" value="${qty}" readonly>
                            <button type="button" onclick="changeDefaultQty(1)">+</button>
                        </div>
                        <span>${new Intl.NumberFormat('ko-KR').format(total)}</span>
                    </div>`;
            }
            
            total += subOptionsTotal;

            const formattedTotal = new Intl.NumberFormat('ko-KR').format(total) + '원';
            document.getElementById('total_price').innerText = formattedTotal;
            document.getElementById('quick_total_price').innerText = formattedTotal;
        }

        // Helper for default quantity sync
        function changeDefaultQty(delta) {
            const qtyInput = document.getElementById('default_qty');
            if (!qtyInput) return;
            let newQty = parseInt(qtyInput.value) + delta;
            if (newQty < 1) newQty = 1;
            qtyInput.value = newQty;
            updateTotal();
        }


        function validateForm() {
            // Check options if exist
            if (hasOptions && Object.keys(selectedOptions).length === 0) {
                alert('옵션을 선택해주세요.'); return false;
            }

            // Text inputs validation (Scoped to goodsForm)
            const form = document.forms['goodsForm'];
            if (form) {
                const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
                for (let input of requiredInputs) {
                    if (!input.value.trim()) {
                        alert('필수 입력 항목을 확인해주세요.');
                        input.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        function processOrder() {
            if (!validateForm()) return;
            // Try getting form by name first, then ID
            let form = document.forms['goodsForm'];
            if (!form) form = document.getElementById('goodsForm');

            if (form) {
                form.submit();
            } else {
                alert('주문 폼(goodsForm)을 찾을 수 없습니다.');
                console.error('Form not found');
            }
        }

        function closeCartModal() {
            document.getElementById('cart_confirm_modal').style.display = 'none';
        }

        function addFileRow() {
            const rows = document.querySelectorAll('.printing-image-row');
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display === 'none') {
                    rows[i].style.display = 'table-row';
                    return;
                }
            }
            alert('최대 10개까지 등록 가능합니다.');
        }

        function removeFileRow() {
            const rows = document.querySelectorAll('.printing-image-row');
            // Iterate backwards, identifying the last visible row
            for (let i = rows.length - 1; i > 0; i--) { // Start from last, stop at index 1 (keep index 0 visible)
                if (rows[i].style.display !== 'none') {
                    rows[i].style.display = 'none';
                    // Reset input value
                    const input = rows[i].querySelector('input[type="file"]');
                    if (input) input.value = '';
                    return;
                }
            }
        }

        function updateTotal() {
            let total = 0;
            let totalQty = 0; // Track total quantity
            const hiddenContainer = document.getElementById('form_hidden_inputs');
            hiddenContainer.innerHTML = '';

            // Sync to Quick View
            const quickDev = document.getElementById('quick_selected_dev');
            quickDev.innerHTML = '';

            let subOptionsTotal = 0;
            for (const [seq, item] of Object.entries(selectedSubOptions)) {
                subOptionsTotal += (item.price * item.qty);
                // Suboptions don't usually count towards main "qty" in legacy unless specified, 
                // but for "min purchase" check they might. For "Purchase Amount" row (1개 X ...), 
                // it usually tracks the MAIN item quantity.
                
                hiddenContainer.innerHTML += `<input type="hidden" name="suboption_seq[]" value="${seq}"><input type="hidden" name="suboption_ea[]" value="${item.qty}">`;
                
                quickDev.innerHTML += `
                    <div class="quick-opt-row">
                        <span style="width:40%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">[추가] ${item.name}</span>
                        <div class="quick-qty-ctrl">
                            <button type="button" onclick="changeQty('${seq}', -1, 'suboption')">-</button>
                            <input type="text" value="${item.qty}" readonly>
                            <button type="button" onclick="changeQty('${seq}', 1, 'suboption')">+</button>
                        </div>
                        <span style="font-size:11px;">${new Intl.NumberFormat('ko-KR').format(item.price * item.qty)}</span>
                        <button type="button" onclick="removeOption('${seq}', 'suboption')" style="border:none; bg:none; cursor:pointer;">x</button>
                    </div>`;
            }

            if (hasOptions) {
                // Render Options
                for (const [seq, item] of Object.entries(selectedOptions)) {
                    let basePrice = item.price;
                    let finalUnitPrice = basePrice;
                    let qty = item.qty;
                    totalQty += qty;

                    if (hundredEa > 0 && qty >= hundredEa) {
                        finalUnitPrice = basePrice - discount100;
                    } else if (fiftyEa > 0 && qty >= fiftyEa) {
                        finalUnitPrice = basePrice - discount50;
                    } else {
                        finalUnitPrice = basePrice - discountMtype;
                    }

                    total += finalUnitPrice * qty;
                    hiddenContainer.innerHTML += `<input type="hidden" name="option_seq[]" value="${seq}"><input type="hidden" name="ea[]" value="${qty}">`;

                    // Add Interactive Row to Quick View
                    quickDev.innerHTML += `
                        <div class="quick-opt-row">
                            <span style="width:40%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${item.name}</span>
                            <div class="quick-qty-ctrl">
                                <button type="button" onclick="changeQty('${seq}', -1, 'option')">-</button>
                                <input type="text" value="${item.qty}" readonly>
                                <button type="button" onclick="changeQty('${seq}', 1, 'option')">+</button>
                            </div>
                            <span style="font-size:11px;">${new Intl.NumberFormat('ko-KR').format(finalUnitPrice * qty)}</span>
                            <button type="button" onclick="removeOption('${seq}', 'option')" style="border:none; bg:none; cursor:pointer;">x</button>
                        </div>`;
                }
            } else {
                const qtyInput = document.getElementById('default_qty');
                let qty = qtyInput ? parseInt(qtyInput.value) : 1;
                totalQty += qty;

                let basePrice = priceInfo.ori_price || 0;
                let finalUnitPrice = basePrice;

                if (hundredEa > 0 && qty >= hundredEa) {
                    finalUnitPrice = basePrice - discount100;
                } else if (fiftyEa > 0 && qty >= fiftyEa) {
                    finalUnitPrice = basePrice - discount50;
                } else {
                    finalUnitPrice = basePrice - discountMtype;
                }

                total = finalUnitPrice * qty;
                hiddenContainer.innerHTML += `<input type="hidden" name="option_seq[]" value="${defaultSeq}"><input type="hidden" name="ea[]" value="${qty}">`;

                // Sync for no-option:
                quickDev.innerHTML += `
                    <div class="quick-opt-row">
                        <span>수량</span>
                        <div class="quick-qty-ctrl">
                            <button type="button" onclick="changeDefaultQty(-1)">-</button>
                            <input type="text" value="${qty}" readonly>
                            <button type="button" onclick="changeDefaultQty(1)">+</button>
                        </div>
                        <span>${new Intl.NumberFormat('ko-KR').format(total)}</span>
                    </div>`;
            }
            
            total += subOptionsTotal;

            const formattedTotal = new Intl.NumberFormat('ko-KR').format(total) + '원';
            const formattedTotalNum = new Intl.NumberFormat('ko-KR').format(total);
            
            // Update Main Total
            document.getElementById('total_price').innerText = formattedTotal;
            document.getElementById('quick_total_price').innerText = formattedTotal;

            // Update Purchase Amount Row
            const firstLabel = document.querySelector('.first_label');
            const firstPrice = document.querySelector('.first_price'); // Unit Price? Calculate average?
            const firstTotPrice = document.querySelector('.first_totprice');
            
            if(firstLabel) firstLabel.innerText = totalQty;
            if(firstTotPrice) firstTotPrice.innerText = formattedTotalNum;
            
            // Calculate pseudo unit price for display: Total / Qty
            // This is just a visual approximation if multiple options with diff prices are selected.
            if(firstPrice && totalQty > 0) {
                 firstPrice.innerText = new Intl.NumberFormat('ko-KR').format(Math.round(total / totalQty));
            } else if (firstPrice) {
                 firstPrice.innerText = '0';
            }
        }
            const formData = new FormData(form);

            fetch("{{ route('cart.store') }}", {
                method: "POST",
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                credentials: 'include', // Ensure cookies are sent
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.status === 'success') {
                    // Show Custom Modal instead of confirm
                    const modal = document.getElementById('cart_confirm_modal');
                    modal.style.display = 'flex';
                } else {
                    alert(data.message || 'Error');
                }
            }).catch(e => {
                console.error(e);
                alert('장바구니 담기 실패');
            });
        }
    </script>
@endsection