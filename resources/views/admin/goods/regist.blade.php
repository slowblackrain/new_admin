
@extends('admin.layouts.admin')

@section('custom_js')
    <input type="hidden" id="goodscd" value="{{ $goods->goods_scode ?? '' }}">
    <input type="hidden" id="goods_rate" value="{{ $goods->goods_rate ?? 0 }}">
    
    <script>
        // CBM Calculation Logic
        function calculateCBM() {
            var weight_val = parseFloat($('input[name="goodsContents2[9]"]').val()) || 0;
            var cbm = weight_val * 0.00022; 
        }

        $(document).ready(function() {
            // Trigger on weight change
            $(document).on('keyup change', 'input[name="goodsContents2[9]"]', function() {
                calculateCBM();
            });
            
            // Toggle Option Mode
            $('input[name="optionUse"]').change(function() {
                toggleOptionMode();
            });
            toggleOptionMode(); // Init
            
            // AJAX Price Calculation Trigger
            var timer;
            $('.cost-input').on('keyup change', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    calculatePrices();
                }, 500); 
            });

            // Initialize Events for Tiered Pricing
            $(document).on('keyup change', '#s_supply_price', function() {
                calcTieredPricing();
            });
            
            // Also trigger when Goods Code changes
            $(document).on('keyup change', 'input[name="goodsScode"]', function() {
                calcTieredPricing();
            });
            
            // Trigger on load if value exists
            if($('#s_supply_price').val()) calcTieredPricing();

            // Manual Tab Handler
            $(document).on('click', '.nav-tabs .nav-link', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });

        // Toggle Option Mode
        function toggleOptionMode() {
            const isMulti1 = $('input[name="optionUse"][value="1"]').is(':checked');
            if (isMulti1) {
                $('#singleOptionMode').hide();
                $('#multiOptionMode').show();
            } else {
                $('#singleOptionMode').show();
                $('#multiOptionMode').hide();
                calcSinglePrice(); // Recalculate
            }
        }

        // Calculate Single Price (Manual)
        function calcSinglePrice() {
            const price = parseInt($('#s_price').val()) || 0;
            const supply = parseInt($('#s_supply_price').val()) || 0;
            const margin = price - supply;
            const marginRate = price > 0 ? ((margin / price) * 100).toFixed(1) : 0;
            
            $('#s_margin_display').text(`${margin.toLocaleString()}원 (${marginRate}%)`);
        }

        // Generate Option Grid
        function generateOptionGrid() {
            const optName = $('#option_name_input').val();
            const optValuesRaw = $('#option_value_input').val();
            
            if (!optName || !optValuesRaw) {
                alert('옵션명과 옵션값을 입력해주세요.');
                return;
            }

            const optValues = optValuesRaw.split(',').map(v => v.trim()).filter(v => v);
            if (optValues.length === 0) return;

            const tbody = document.querySelector('#optionGridTable tbody');
            tbody.innerHTML = ''; 

            const supply = $('input[name="supplyPrice[]"]').val() || 0;
            const price = $('input[name="price[]"]').val() || 0;
            const consumer = $('input[name="consumerPrice[]"]').val() || 0;

            optValues.forEach((val, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="text" class="form-control form-control-sm bg-light" name="optionTitle[]" value="${val}" readonly></td>
                    <td><input type="number" class="form-control form-control-sm" name="supplyPrice[]" value="${supply}"></td>
                    <td><input type="number" class="form-control form-control-sm" name="consumerPrice[]" value="${consumer}"></td>
                    <td><input type="number" class="form-control form-control-sm" name="price[]" value="${price}"></td>
                    <td><input type="number" class="form-control form-control-sm" name="stock[]" value="999"></td>
                    <td><input type="number" class="form-control form-control-sm" name="safe_stock[]" value="0"></td>
                    <td><input type="number" class="form-control form-control-sm" name="reserve[]" value="0"></td>
                    <td><input type="number" class="form-control form-control-sm" name="commissionRate[]" value="0"></td>
                    <td>
                        <select class="form-select form-select-sm" name="optionStatus[]">
                            <option value="normal">정상</option>
                            <option value="runout">품절</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">삭제</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function calculatePrices() {
            var formData = {
                realCost: $('input[name="realCost"]').val(),
                exchange: $('input[name="exchange"]').val(),
                customs: $('input[name="customs"]').val(),
                incidental: $('input[name="incidental"]').val(),
                otherCost_mem: $('input[name="otherCost_mem"]').val(),
                otherCost_sticker: $('input[name="otherCost_sticker"]').val(),
                otherCost_package: $('input[name="otherCost_package"]').val(),
                otherCost_delivery: $('input[name="otherCost_delivery"]').val(),
                otherCost_etc: $('input[name="otherCost_etc"]').val(),
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '{{ route("admin.goods.calculate_price") }}',
                type: 'POST',
                data: formData,
                success: function(res) {
                    $('#calc_base_cost').text(Math.round(res.base_cost).toLocaleString());
                    $('#calc_landed_price').text(Math.round(res.landed_price).toLocaleString());
                    $('#calc_wholesale').text(Math.round(res.wholesale_price).toLocaleString());
                    $('#calc_retail').text(Math.round(res.retail_price).toLocaleString());

                    if (res.retail_price > 0) {
                        $('#s_supply_price').val(res.landed_price);
                        $('#s_price').val(res.retail_price);
                        $('#s_consumer_price').val(Math.round(res.retail_price * 1.5));
                        calcSinglePrice();
                        calcTieredPricing(); // Trigger tiered pricing update
                    }
                },
                error: function(err) { 
                    console.error('Price Calc Error', err); 
                }
            });
        }

        // ============================================================
        // TIERED PRICING LOGIC (STRICT LEGACY RESTORATION)
        // ============================================================
        
        // Strict Legacy Rounding Function: upCeil
        // Matches Dometopia legacy logic: Ends in 5-9 -> round up to next 10. Ends in 0-4 -> round down.
        function upCeil(v) {
            if (!v) return 0;
            var val = String(Math.round(Number(v)));
            if (Number(val.substr(val.length - 1)) > 4) {
                return Number(val.substr(0, val.length - 1)) + 1 + "0";
            } else {
                return val.substr(0, val.length - 1) + "0";
            }
        }

        // Main Calculator Function
        function calcTieredPricing() {
            try {
                var supplyStr = $('#s_supply_price').val();
                if(!supplyStr) return;
                
                // Handle comma removal if present (input type number usually doesn't have it but for safety)
                var supplyPrice = parseInt(String(supplyStr).replace(/,/g, ''));
                if (isNaN(supplyPrice) || supplyPrice <= 0) return;

                // 1. Calculate Base Price
                var goodsRate = parseFloat($('#goods_rate').val()) || 0;
                var basePrice;
                
                if (goodsRate > 0) {
                    basePrice = upCeil(supplyPrice * (1 + goodsRate / 100));
                } else {
                    basePrice = upCeil(supplyPrice * 1.1); // Default 1.1 (10% Margin)
                }

                // 2. Identify Goods Code Branch
                var goodsCode = $('input[name="goodsScode"]').val() || $('#goodscd').val() || '';
                var prefix2 = goodsCode.length >= 2 ? goodsCode.substr(0, 2).toUpperCase() : '';
                var prefix3 = goodsCode.length >= 3 ? goodsCode.substr(0, 3).toUpperCase() : '';
                var suffix1 = goodsCode.length >= 1 ? goodsCode.substr(goodsCode.length - 1, 1).toUpperCase() : '';

                var price50, price100;
                var qty50 = 50, qty100 = 100; // Defaults
                var branchName = 'F (Default)';

                if (prefix3 === 'ATQ' || prefix2 === 'B') {
                    branchName = 'A (No Discount)';
                    price50 = basePrice;
                    price100 = basePrice;
                    qty50 = 0; qty100 = 0;
                } 
                else if (prefix3 === 'GDR') {
                    branchName = 'B (GDR Retail)';
                    price50 = basePrice; price100 = basePrice;
                }
                else if (prefix3 === 'GTH' || suffix1 === 'P') {
                    branchName = 'C/D (Factory/P)';
                    price50 = basePrice; price100 = basePrice;
                    qty50 = 1; qty100 = 1;
                }
                else if (prefix2 === 'GT' || prefix2 === 'XT') {
                    branchName = 'E (GT/XT High Discount)';
                    price50 = upCeil(basePrice * 0.90);
                    price100 = upCeil(basePrice * 0.80);
                    if(price50 > 0) qty50 = parseInt(500000 / price50) + 1;
                    if(price100 > 0) qty100 = parseInt(1000000 / price100) + 1;
                } 
                else {
                    branchName = 'F (Standard 95/90)';
                    price50 = upCeil(basePrice * 0.95);
                    price100 = upCeil(basePrice * 0.90);
                    if(price50 > 0) qty50 = parseInt(500000 / price50) + 1;
                    if(price100 > 0) qty100 = parseInt(1000000 / price100) + 1;
                }

                // console.log('Tiered Pricing:', { branch: branchName, base: basePrice, p50: price50, p100: price100 });

                $('input[name="fifty_discount"]').val(price50);
                $('input[name="fifty_discount_ea"]').val(qty50);
                $('input[name="hundred_discount"]').val(price100);
                $('input[name="hundred_discount_ea"]').val(qty100);
            } catch (e) {
                console.error("calcTieredPricing error:", e);
            }
        }

        // Expose functions to global scope for inline handlers
        window.calculateCBM = calculateCBM;
        window.toggleOptionMode = toggleOptionMode;
        window.calcSinglePrice = calcSinglePrice;
        window.generateOptionGrid = generateOptionGrid;
        window.calculatePrices = calculatePrices;
        window.upCeil = upCeil;
        window.calcTieredPricing = calcTieredPricing;
    </script>

@endsection

@section('page-script')
    <!-- Legacy placeholder if needed -->
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">상품 등록</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="goodsRegist" name="goodsRegist" action="{{ route('admin.goods.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#basic" role="tab">기본 정보</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#category" role="tab">카테고리/분류</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#option" role="tab">가격/재고/옵션</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#detail" role="tab">상세설명/이미지</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#memo" role="tab">관리</a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        
                        <!-- Tab 1: Basic Info -->
                        <div class="tab-pane active" id="basic" role="tabpanel">
                            
                            <!-- Section: Status & Classification -->
                            <div class="card mb-3 shadow-sm border">
                                <div class="card-header bg-white font-weight-bold">
                                    <i class="fas fa-toggle-on me-1"></i> 상태 및 분류 설정
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">상품 승인</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="provider_status" value="1" checked>
                                                    <label class="form-check-label">승인</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="provider_status" value="0">
                                                    <label class="form-check-label">미승인</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">노출 상태</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="goodsView" value="look" checked>
                                                    <label class="form-check-label">노출</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="goodsView" value="notLook">
                                                    <label class="form-check-label">미노출</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">판매 상태</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="goodsStatus" value="normal" checked>
                                                    <label class="form-check-label">정상</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="goodsStatus" value="runout">
                                                    <label class="form-check-label">품절</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="goodsStatus" value="unsold">
                                                    <label class="form-check-label">중지</label>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control form-control-sm mt-1" name="goods_soldout_text" placeholder="품절 시 노출 문구 (예: 재입고 예정)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Product Identity -->
                            <div class="card mb-3 shadow-sm border">
                                <div class="card-header bg-white font-weight-bold">
                                    <i class="fas fa-info-circle me-1"></i> 상품 기본 정보
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">상품명 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="goodsName" required placeholder="기본 상품명을 입력하세요.">
                                            <div class="input-group-text bg-light">
                                                <input class="form-check-input mt-0 me-2" type="checkbox" name="chkGoodsNameLinkage">
                                                <span>연동용 별도 입력</span>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" name="goodsNameLinkage" placeholder="[선택] 오픈마켓 연동용 상품명이 다를 경우 입력하세요.">
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">상품코드 (시스템)</label>
                                            <input type="text" class="form-control bg-light" name="goodsCode" placeholder="저장 시 자동생성" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">자체 상품코드</label>
                                            <input type="text" class="form-control" name="goodsScode" placeholder="판매자 관리 코드">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">진열 우선순위</label>
                                            <input type="number" class="form-control" name="goodsSortcd" placeholder="낮을수록 우선 노출">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">검색 키워드</label>
                                        <input type="text" class="form-control" name="keyword" placeholder="쉼표(,)로 구분하여 입력 (예: 여름,반팔,특가)">
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Details & Specs -->
                            <div class="card mb-3 shadow-sm border">
                                <div class="card-header bg-white font-weight-bold">
                                    <i class="fas fa-list me-1"></i> 상품 상세 스펙
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">제조사</label>
                                            <input type="text" class="form-control" name="maker_name" list="maker_list" placeholder="제조사 입력">
                                            <!-- Datalist for autocomplete if we want to restore suggestions later -->
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">원산지</label>
                                            <input type="text" class="form-control" name="origin_name" placeholder="원산지 입력">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">모델명</label>
                                            <input type="text" class="form-control" name="model" placeholder="모델명 입력">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">과세 구분</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="tax" value="tax" checked>
                                                    <label class="form-check-label">과세</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="tax" value="exempt">
                                                    <label class="form-check-label">비과세</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="tax" value="none">
                                                    <label class="form-check-label">영세</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">HS CODE</label>
                                            <input type="text" class="form-control" name="hscode" placeholder="수출입 코드">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Category -->
                        <div class="tab-pane" id="category" role="tabpanel">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">카테고리 연결</label>
                                <div class="col-sm-10">
                                    <div class="card p-3 bg-light">
                                        <p class="text-muted mb-2">대표 카테고리 선택</p>
                                        <!-- Open Modal with Button -->
                                        <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="openCategoryModal()">카테고리 연결</button>
                                        
                                        <table class="table table-bordered table-sm bg-white">
                                            <thead>
                                                <tr>
                                                    <th style="width:60px;">대표</th>
                                                    <th>카테고리 경로</th>
                                                    <th style="width:60px;">관리</th>
                                                </tr>
                                            </thead>
                                            <tbody id="categoryList">
                                                <!-- Empty Initially -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">브랜드 연결</label>
                                <div class="col-sm-10">
                                    <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="alert('브랜드 팝업 구현 예정')">브랜드 연결</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Options -->
                        <div class="tab-pane" id="option" role="tabpanel">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">옵션 설정</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4 border-bottom pb-4">
                                        <label class="fw-bold mb-3">구매 수량 제한</label>
                                        <div class="row mb-3">
                                            <label class="col-sm-2 col-form-label">최소 구매수량</label>
                                            <div class="col-sm-10">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="minPurchaseLimit" value="unlimit" checked onclick="$('#minPurchaseEaWrapper').hide()">
                                                    <label class="form-check-label">제한 없음 (최소 1개)</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="minPurchaseLimit" value="limit" onclick="$('#minPurchaseEaWrapper').show()">
                                                    <label class="form-check-label">제한함</label>
                                                </div>
                                                <div id="minPurchaseEaWrapper" class="d-inline-block ms-2" style="display:none;">
                                                    <input type="number" class="form-control d-inline-block w-auto" name="minPurchaseEa" value="2" style="width: 80px;"> 개 부터 구매 가능
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-2 col-form-label">최대 구매수량</label>
                                            <div class="col-sm-10">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="maxPurchaseLimit" value="unlimit" checked onclick="$('#maxPurchaseEaWrapper').hide()">
                                                    <label class="form-check-label">제한 없음</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="maxPurchaseLimit" value="limit" onclick="$('#maxPurchaseEaWrapper').show()">
                                                    <label class="form-check-label">제한함</label>
                                                </div>
                                                <div id="maxPurchaseEaWrapper" class="d-inline-block ms-2" style="display:none;">
                                                    <input type="number" class="form-control d-inline-block w-auto" name="maxPurchaseEa" value="5" style="width: 80px;"> 개 까지 구매 가능
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label class="col-sm-2 col-form-label fw-bold">적립금 정책</label>
                                        <div class="col-sm-10">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="reserve_policy" value="shop" checked>
                                                <label class="form-check-label">기본 정책 (쇼핑몰 통합)</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="reserve_policy" value="goods">
                                                <label class="form-check-label">개별 정책 (상품별)</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Volume Discount Section -->
                                    <div class="card mb-4 border">
                                        <div class="card-header bg-light fw-bold">
                                            <i class="fas fa-boxes me-1"></i> 대량 구매 할인 설정 (Volume Discount)
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <label class="col-sm-2 col-form-label">할인 적용 여부</label>
                                                <div class="col-sm-10">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="multi_discount_use" value="0" checked onclick="$('#multiDiscountConfig').hide()">
                                                        <label class="form-check-label">사용안함</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="multi_discount_use" value="1" onclick="$('#multiDiscountConfig').show()">
                                                        <label class="form-check-label">사용함</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="multiDiscountConfig" style="display:none;">
                                                <div class="alert alert-secondary py-2">
                                                    <small>※ 특정 수량 이상 구매 시 추가 할인을 적용합니다.</small>
                                                </div>
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <span class="fw-bold">1단계:</span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount_ea" style="width: 80px;" value="0"> 개 이상 구매 시 
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount" style="width: 100px;" value="0">
                                                    </div>
                                                    <div class="col-auto">
                                                        <select class="form-control form-control-sm" name="multi_discount_unit">
                                                            <option value="won">원 할인</option>
                                                            <option value="percent">% 할인</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <span class="fw-bold">2단계:</span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount_ea1" style="width: 80px;" value="0"> 개 이상 구매 시 
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount1" style="width: 100px;" value="0">
                                                    </div>
                                                    <div class="col-auto">
                                                        <select class="form-control form-control-sm" name="multi_discount_unit1">
                                                            <option value="won">원 할인</option>
                                                            <option value="percent">% 할인</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="fw-bold">3단계:</span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount_ea2" style="width: 80px;" value="0"> 개 이상 구매 시 
                                                    </div>
                                                    <div class="col-auto">
                                                        <input type="number" class="form-control form-control-sm d-inline-block" name="multi_discount2" style="width: 100px;" value="0">
                                                    </div>
                                                    <div class="col-auto">
                                                        <select class="form-control form-control-sm" name="multi_discount_unit2">
                                                            <option value="won">원 할인</option>
                                                            <option value="percent">% 할인</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cost Calculation Section -->
                                    <div class="card bg-light mb-4 border">
                                        <div class="card-header bg-white fw-bold text-primary">
                                            <i class="fas fa-calculator me-1"></i> 원가 및 판매가 자동 계산
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small">실원가 (Real Cost)</label>
                                                    <input type="number" class="form-control form-control-sm cost-input" name="realCost" id="realCostInput" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">환율 (Exchange)</label>
                                                    <input type="number" class="form-control form-control-sm cost-input" name="exchange" value="1">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">관세율 (Duty 0.08)</label>
                                                    <input type="number" class="form-control form-control-sm cost-input" name="customs" value="0.08" step="0.01">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">부대비용율 (Incidental 1.05)</label>
                                                    <input type="number" class="form-control form-control-sm cost-input" name="incidental" value="1.05" step="0.01">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-primary w-100 mb-1" onclick="calculatePrices(); calcTieredPricing();">계산</button>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold">추가 비용 (Other Costs)</label>
                                                    <div class="d-flex gap-2">
                                                        <input type="number" class="form-control form-control-sm cost-input" name="otherCost_mem" placeholder="인건비" value="0" title="인건비">
                                                        <input type="number" class="form-control form-control-sm cost-input" name="otherCost_sticker" placeholder="스티커" value="0" title="스티커">
                                                        <input type="number" class="form-control form-control-sm cost-input" name="otherCost_package" placeholder="포장비" value="0" title="포장비">
                                                        <input type="number" class="form-control form-control-sm cost-input" name="otherCost_delivery" placeholder="배송비" value="0" title="배송비">
                                                        <input type="number" class="form-control form-control-sm cost-input" name="otherCost_etc" placeholder="기타" value="0" title="기타">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning py-2 mb-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge bg-secondary me-2">계산 결과</span>
                                                    기준원가: <strong id="calc_base_cost">0</strong>원 / 
                                                    수입원가(공급가): <strong id="calc_landed_price" class="text-danger">0</strong>원
                                                </div>
                                                <div>
                                                    권장 도매가: <strong id="calc_wholesale">0</strong>원 / 
                                                    권장 소비자가: <strong id="calc_retail" class="text-primary">0</strong>원
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold me-3">옵션 사용 여부</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="optionUse" value="0" checked onchange="toggleOptionMode()">
                                            <label class="form-check-label">사용안함 (단일옵션)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="optionUse" value="1" onchange="toggleOptionMode()">
                                            <label class="form-check-label">사용함 (멀티옵션)</label>
                                        </div>
                                    </div>

                                    <!-- Single Option Mode -->
                                    <div id="singleOptionMode">
                                        <div class="row mb-3">
                                            <label class="col-sm-2 col-form-label">정가 (소비자가)</label>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="consumerPrice[]" id="s_consumer_price" value="0" onkeyup="calcSinglePrice()">
                                            </div>
                                            <label class="col-sm-2 col-form-label">판매가</label>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="s_price" value="0" required onkeyup="calcSinglePrice()">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-2 col-form-label">매입가 (공급가)</label>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="supplyPrice[]" id="s_supply_price" value="0" onkeyup="calcSinglePrice(); calcTieredPricing();">
                                            </div>
                                            <label class="col-sm-2 col-form-label">재고</label>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="stock[]" value="999">
                                                <!-- Single option hidden fields -->
                                                <input type="hidden" name="optionTitle[]" value="단품">
                                            </div>
                                        </div>
    <div class="row mb-3">
                                            <label class="col-sm-2 col-form-label">마진 / 수수료</label>
                                            <div class="col-sm-4 d-flex align-items-center">
                                                <span id="s_margin_display" class="fw-bold text-primary me-3">0원 (0%)</span>
                                                <input type="number" class="form-control w-25 d-inline-block" name="commissionRate[]" value="0" placeholder="수수료%"> %
                                            </div>
                                        </div>
                                        
                                        <!-- Tiered Pricing (Wholesale Discount / Factory Trade) -->
                                        <div class="card bg-white border mb-3">
                                            <div class="card-header py-2 bg-light font-weight-bold low-pad">
                                                <small class="text-dark"><i class="fas fa-tags me-1"></i> 대량/무역 단가 설정 (Tiered Pricing)</small>
                                            </div>
                                            <div class="card-body py-2">
                                                <div class="row mb-2 align-items-center">
                                                    <label class="col-sm-2 col-form-label text-muted small">도매 할인가 (Tier 1)</label>
                                                    <div class="col-sm-4">
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control" name="fifty_discount_ea" value="50" style="max-width: 60px;">
                                                            <span class="input-group-text">개 이상</span>
                                                            <input type="number" class="form-control" name="fifty_discount" value="0" placeholder="단가 입력">
                                                            <span class="input-group-text">원</span>
                                                        </div>
                                                    </div>
                                                    <label class="col-sm-2 col-form-label text-muted small">무역/공장도가 (Tier 2)</label>
                                                    <div class="col-sm-4">
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control" name="hundred_discount_ea" value="100" style="max-width: 60px;">
                                                            <span class="input-group-text">개 이상</span>
                                                            <input type="number" class="form-control" name="hundred_discount" value="0" placeholder="단가 입력">
                                                            <span class="input-group-text">원</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Multi Option Mode -->
                                    <div id="multiOptionMode" style="display:none;">
                                        <div class="alert alert-info py-2">
                                            옵션명을 입력하고 [옵션 목록 생성]을 클릭하세요.
                                        </div>
                                        <div class="row mb-3 align-items-end">
                                            <div class="col-3">
                                                <label class="form-label">옵션명 (예: 사이즈, 색상)</label>
                                                <input type="text" class="form-control" id="option_name_input" placeholder="예: 사이즈">
                                            </div>
                                            <div class="col-5">
                                                <label class="form-label">옵션값 (쉼표로 구분)</label>
                                                <input type="text" class="form-control" id="option_value_input" placeholder="예: S,M,L">
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-secondary w-100" onclick="generateOptionGrid()">목록 생성</button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm text-center align-middle" id="optionGridTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>옵션명</th>
                                                        <th>매입가</th>
                                                        <th>정가</th>
                                                        <th>판매가</th>
                                                        <th>재고</th>
                                                        <th>안전재고</th>
                                                        <th>적립금</th>
                                                        <th>수수료(%)</th>
                                                        <th>상태</th>
                                                        <th>관리</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Dynamic Rows -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Detail & Images -->
                        <div class="tab-pane" id="detail" role="tabpanel">
                            <div class="card mb-3">
                                <div class="card-header bg-light">이미지 등록</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">대표 이미지</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" name="goodsImage[]" accept="image/*">
                                            <small class="text-muted">권장사이즈: 500x500 px</small>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">추가 이미지</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" name="goodsImage[]" multiple accept="image/*">
                                            <small class="text-muted">여러 장 선택 가능</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header bg-light">상세 설명</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">PC 상세설명</label>
                                        <textarea class="form-control" name="goodscontents" rows="10"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">모바일 상세설명</label>
                                        <textarea class="form-control" name="mobile_contents" rows="10"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 5: Memo -->
                        <div class="tab-pane" id="memo" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">관리자 메모</label>
                                <textarea class="form-control" name="admin_memo" rows="5" placeholder="관리자 전용 메모입니다."></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5">상품 등록</button>
                        <a href="{{ route('admin.goods.catalog') }}" class="btn btn-secondary btn-lg px-5">취소</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<style>
/* Grid Styles */
#optionGridTable th { background-color: #f8f9fa; font-weight: 600; font-size: 13px; }
.nav-tabs-custom .nav-link.active { font-weight: bold; border-top: 3px solid #0d6efd; color: #0d6efd; }
.cat-select { height: 200px; }
</style>

<!-- Category Selection Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">카테고리 연결</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-3">
                        <label class="form-label text-center d-block">대분류 (1차)</label>
                        <select class="form-control cat-select" id="cat_depth1" size="10" onchange="loadChildren(this.value, 2)"></select>
                    </div>
                    <div class="col-3">
                        <label class="form-label text-center d-block">중분류 (2차)</label>
                        <select class="form-control cat-select" id="cat_depth2" size="10" onchange="loadChildren(this.value, 3)"></select>
                    </div>
                    <div class="col-3">
                        <label class="form-label text-center d-block">소분류 (3차)</label>
                        <select class="form-control cat-select" id="cat_depth3" size="10" onchange="loadChildren(this.value, 4)"></select>
                    </div>
                    <div class="col-3">
                        <label class="form-label text-center d-block">세분류 (4차)</label>
                        <select class="form-control cat-select" id="cat_depth4" size="10" onchange="selectFinal(this.value)"></select>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    선택된 카테고리: <strong id="selectedCategoryPath" class="text-primary">없음</strong>
                    <input type="hidden" id="selectedCategoryCode">
                    <input type="hidden" id="selectedCategoryName">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" onclick="addCategoryToTable()">추가하기</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Open Modal
    function openCategoryModal() {
        var myModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        myModal.show();
        loadChildren(0, 1); // Load Root (Depth 1)
        $('#selectedCategoryPath').text('없음');
        $('#selectedCategoryCode').val('');
    }

    // Load Children via AJAX
    function loadChildren(parentId, depth) {
        // Reset lower depths
        for(let i=depth; i<=4; i++) {
            $(`#cat_depth${i}`).empty();
        }
        
        if (!parentId) return;

        // Update Text (Breadcrumb)
        updateBreadcrumb();

        $.ajax({
            url: '{{ route("admin.goods.category_children") }}',
            type: 'POST',
            data: { 
                parent_id: parentId,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                let target = $(`#cat_depth${depth}`);
                target.empty();
                if(res.length > 0) {
                    res.forEach(cat => {
                        target.append(`<option value="${cat.id}" data-code="${cat.category_code}" data-name="${cat.title}">${cat.title}</option>`);
                    });
                }
            }
        });
    }

    function selectFinal(val) {
        updateBreadcrumb();
    }

    function updateBreadcrumb() {
        let path = [];
        let lastCode = '';
        
        for(let i=1; i<=4; i++) {
            let el = document.getElementById(`cat_depth${i}`);
            if (el.selectedIndex >= 0) {
                let opt = el.options[el.selectedIndex];
                path.push(opt.text);
                lastCode = opt.getAttribute('data-code');
            }
        }
        
        if (path.length > 0) {
            $('#selectedCategoryPath').text(path.join(' > '));
            $('#selectedCategoryName').val(path.join(' > '));
            $('#selectedCategoryCode').val(lastCode);
        }
    }

    function addCategoryToTable() {
        let code = $('#selectedCategoryCode').val();
        let name = $('#selectedCategoryName').val();
        
        if (!code) {
            alert('카테고리를 선택해주세요.');
            return;
        }

        // Check Duplicate
        let exists = false;
        $('input[name="categoryCodes[]"]').each(function() {
            if ($(this).val() == code) exists = true;
        });

        if (exists) {
            alert('이미 추가된 카테고리입니다.');
            return;
        }

        // Add Row
        let html = `
            <tr>
                <td class="text-center"><input type="radio" name="firstCategory" value="${code}" checked></td>
                <td>
                    ${name}
                    <input type="hidden" name="categoryCodes[]" value="${code}">
                </td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-xs" onclick="this.closest('tr').remove()">삭제</button></td>
            </tr>
        `;
        
        // Remove 'No Data' row if exists (placeholder)
        // Adjust logic: assume table might be empty or have sample row
        // For now, assume user manages list.
        
        $('#categoryList').append(html);
        
        // Close Modal
        bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
    }
</script>

@endsection
