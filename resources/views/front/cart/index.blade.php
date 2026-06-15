@extends('layouts.front')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="/css/order.css?v={{ time() }}">
        <style>
            /* Legacy Custom styles for Table and Badges */
            .icon-lbl {
                background: #eff2f4;
                border: 1px solid #ced4da;
                padding: 1px 4px;
                font-size: 11px;
                color: #495057;
                border-radius: 2px;
                margin-right: 3px;
                font-weight: bold;
                display: inline-block;
            }
            .cart_table th {
                background: #f8f9fa !important;
                color: #333 !important;
                border-bottom: 2px solid #ccc !important;
                font-size: 13px !important;
                padding: 10px 5px !important;
            }
            .cart_table td {
                padding: 12px 8px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #eee !important;
            }
            .btn_qty_mod_legacy {
                background: #2b77f3;
                color: #fff;
                border: 1px solid #2b77f3;
                padding: 2px 8px;
                font-size: 12px;
                border-radius: 2px;
                cursor: pointer;
                display: inline-block;
                margin-top: 5px;
                font-weight: bold;
            }
            .btn_qty_mod_legacy:hover {
                background: #1557c0;
            }
            .badge_scode {
                color: #2b77f3;
                font-weight: bold;
                font-size: 12px;
                margin-right: 6px;
            }
            .btn_del_legacy {
                background: #d32f2f;
                color: #fff;
                border: none;
                padding: 2px 6px;
                font-size: 11px;
                border-radius: 2px;
                cursor: pointer;
                font-weight: bold;
            }
            .btn_del_legacy:hover {
                background: #b71c1c;
            }
            .op_change_btn_legacy {
                background: #fff;
                border: 1px solid #ccc;
                color: #555;
                font-size: 11px;
                padding: 1px 5px;
                margin-top: 4px;
                cursor: pointer;
                border-radius: 2px;
            }
            .cart_total_area_v2 {
                background: #f8f9fa;
                border: 1px solid #ddd;
                padding: 15px 25px;
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 4px;
            }
            .circle-op {
                background: #e9ecef;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 15px;
                color: #777;
                border: 1px solid #ced4da;
            }
        </style>
    @endpush
    <div class="order_header_v2">
        <div class="order_header_inner clearbox">
            <!-- Left: Title with Icon -->
            <div class="title_area">
                <h2>장바구니<i><img src="https://dometopia.com/data/skin/beauty/images/icon/order_card.png" alt="Cart Icon" style="vertical-align: middle; width: 36px; height: 36px;"></i></h2>
            </div>
            
            <!-- Right: Step Indicator -->
            <div class="step_area">
                <ul>
                    <li class="on" style="position: relative;"><span style="position: absolute; top: -14px; left: 0; font-size: 10px; color: #ff5722; font-weight: bold;">step</span><span class="num">1</span> <span class="txt">장바구니</span></li>
                    <li><span class="num">2</span> <span class="txt">주문/결제</span></li>
                    <li><span class="num">3</span> <span class="txt">주문완료</span></li>
                </ul>
            </div>
        </div>
    </div>

        <div class="cart_list_area">
            <!-- Upper buttons: Legacy layout -->
            <div class="cart_button_wrap" style="margin-bottom: 12px; display: flex; justify-content: flex-start; gap: 6px; align-items: center;">
                <button type="button" class="btn_chk_all_toggle" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 2px; cursor: pointer; color:#333; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">상품 전체 선택/해제</button>
                <button type="button" class="btn_select_del" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 2px; cursor: pointer; color:#333; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">선택삭제</button>
                <button type="button" class="btn_save_wishlist" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 2px; cursor: pointer; color:#333; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'" onclick="alert('선택한 상품이 위시리스트에 저장되었습니다.');">위시리스트저장</button>
                @if(config('shop.shipping.useestimate', 'Y') === 'Y')
                    <button type="button" class="btn_print_estimate" style="height: 32px; padding: 0 16px; background: #2b77f3; border: 1px solid #2b77f3; font-size: 13px; border-radius: 2px; cursor: pointer; color:#fff; font-weight:bold; transition: all 0.2s;" onmouseover="this.style.background='#1557c0'" onmouseout="this.style.background='#2b77f3'" onclick="window.open('/prints/form_print_estimate?code=cart', '_estimate', 'width=960,height=640,scrollbar=1');">견적서</button>
                @endif
            </div>

            <form name="cartForm" id="cartForm" method="post" action="">
                @csrf

                <!-- Legacy 7 Column Table Layout -->
                <table class="cart_table" style="width: 100%; border-collapse: collapse; border-top: 2px solid #ccc;">
                    <colgroup>
                        <col width="45" />
                        <col width="*" />
                        <col width="90" />
                        <col width="110" />
                        <col width="90" />
                        <col width="110" />
                        <col width="160" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;"><input type="checkbox" id="chk_all" checked></th>
                            <th scope="col" style="text-align: center;">주문상품</th>
                            <th scope="col" style="text-align: center;"><span class="icon-lbl">N</span>수량</th>
                            <th scope="col" style="text-align: center;"><span class="icon-lbl">W</span>상품금액</th>
                            <th scope="col" style="text-align: center;"><span class="icon-lbl">%</span>할인</th>
                            <th scope="col" style="text-align: center;"><span class="icon-lbl">W</span>주문금액</th>
                            <th scope="col" style="text-align: center;"><span class="icon-lbl">S</span>배송</th>
                        </tr>
                    </thead>
                    @php
                        $totalPrice = 0;
                        $totalVat = $totalVat ?? 0;
                    @endphp
                    @forelse($groupedCart as $groupKey => $group)
                    <tbody class="shipping-group" data-group-key="{{ $groupKey }}">
                        <tr class="group-header" style="background-color: #f8f9fa;">
                            <td colspan="7" style="text-align: left; padding: 10px 15px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ddd; color: #555;">
                                <i class="fas fa-truck" style="margin-right: 5px;"></i> {{ $group['name'] }}
                            </td>
                        </tr>
                        @php $totalPrice = 0; @endphp
                        @forelse($group['items'] as $item)
                            @php
                                // Use Pre-calculated Pricing Info from Controller
                                $goods = $item->goods;
                                $pricing = $item->pricing_info;
                                $price = $pricing['unit_price'];
                                $itemPrice = $pricing['total_price'];
                                $ea = $item->options->first()->ea ?? 1;

                                $option = $item->options->first();
                                $optionParts = [];
                                if ($option->option1) $optionParts[] = $option->option1;
                                if ($option->option2) $optionParts[] = $option->option2;
                                if ($option->option3) $optionParts[] = $option->option3;
                                if ($option->option4) $optionParts[] = $option->option4;
                                if ($option->option5) $optionParts[] = $option->option5;
                                $optionStr = implode(' / ', $optionParts);

                                $totalPrice += $itemPrice;

                                $mainImage = $goods->images->where('image_type', 'thumbCart')->first() 
                                    ?? $goods->images->where('image_type', 'list1')->first()
                                    ?? $goods->images->where('image_type', 'view')->first();
                                
                                $imagePath = $mainImage ? $mainImage->image : '';
                                $imgSrc = '/images/no_image.gif';
                                
                                if ($imagePath) {
                                    $imagePath = trim($imagePath);
                                    if (Str::startsWith($imagePath, 'http')) {
                                        $imgSrc = $imagePath;
                                    } elseif (strpos($imagePath, 'goods_img') !== false) {
                                        $suffix = substr($imagePath, strpos($imagePath, 'goods_img') + 9);
                                        $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                                    } elseif (strpos($imagePath, '/data/goods/') === 0) {
                                        $imgSrc = "http://dometopia.com" . $imagePath;
                                    } else {
                                        $imgSrc = "http://dometopia.com/data/goods/" . $imagePath;
                                    }
                                }
                            @endphp
                            <tr data-cart-seq="{{ $item->cart_seq }}" data-price="{{ $price }}" data-is-postpaid="{{ $item->is_postpaid ? 1 : 0 }}" data-tax="{{ $goods->tax ?? 'tax' }}" data-group-key="{{ $groupKey }}" data-point="{{ $item->point ?? 0 }}">
                                <td style="text-align: center;"><input type="checkbox" name="cart_seq[]" class="chk_item" value="{{ $item->cart_seq }}" checked></td>
                                <td class="info_cell" style="text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <!-- Image Link -->
                                        <a href="{{ route('goods.view', ['no' => $goods->goods_seq]) }}" style="display: block; width: 60px; height: 60px; flex-shrink: 0; border: 1px solid #ddd; border-radius: 2px; overflow: hidden;">
                                            <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/images/no_image.gif'">
                                        </a>
                                        
                                        <!-- Info Details -->
                                        <div>
                                            <div style="margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                <span class="badge_scode">{{ $goods->goods_scode }}</span>
                                                <!-- Delete button beside scode in Legacy -->
                                                <button type="button" class="btn_del_legacy btn_del">삭제</button>
                                            </div>
                                            <div class="g_name" style="font-weight: bold; color: #333; font-size: 13px;">
                                                {{ $goods->goods_name }}
                                                @if($item->is_postpaid)
                                                    <span class="badge_postpaid" style="color:red; font-size:11px; border:1px solid red; padding:0 2px; margin-left: 4px;">[착불]</span>
                                                @endif
                                            </div>
                                            @if($optionStr && $optionStr != '기본')
                                                <div class="g_opt" style="font-size: 12px; color: #666; margin-top: 2px;">옵션: {{ $optionStr }}</div>
                                            @endif

                                            {{-- Input Fields Display --}}
                                            @if($item->inputs->count() > 0)
                                                <div class="g_inputs display_inputs_area" style="margin-top: 6px; font-size: 12px; line-height: 1.6;">
                                                    @foreach($item->inputs as $input)
                                                        <div class="input_row" style="display: flex; align-items: center; margin-bottom: 2px;">
                                                            <i class="fas fa-edit" style="color: #9eabbb; margin-right: 4px; font-size: 11px;"></i>
                                                            <strong style="color: #9eabbb; font-weight: bold; margin-right: 4px;">{{ $input->input_title }}:</strong>
                                                            @if($input->type == 'file')
                                                                @php
                                                                    $fileUrl = asset('storage/' . $input->input_value);
                                                                    $fileName = basename($input->input_value);
                                                                    $isImage = in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                                @endphp
                                                                <a href="{{ $fileUrl }}" target="_blank" class="file_link" style="display: inline-flex; align-items: center; text-decoration: underline; color: #2b77f3;">
                                                                    @if($isImage)
                                                                        <img src="{{ $fileUrl }}" alt="인쇄이미지" style="width: 16px; height: 16px; object-fit: cover; border: 1px solid #ddd; margin-right: 4px; border-radius: 2px;">
                                                                    @endif
                                                                    <span style="font-size: 11px;">{{ $fileName }} [확인]</span>
                                                                </a>
                                                            @else
                                                                <span style="color: #333;">{{ $input->input_value }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <input type="text" value="{{ $ea }}" class="qty_input" style="width: 35px; text-align: center; height: 22px; border: 1px solid #ccc; font-size: 13px; font-weight: bold;">
                                    <button type="button" class="btn_qty_mod_legacy btn_qty_mod">변경</button>
                                    @if($options_count ?? 1 > 0)
                                        <button type="button" class="op_change_btn_legacy btn_opt_mod" style="display:block; width:100%; margin:4px auto 0;">옵션/수량변경</button>
                                    @endif
                                </td>
                                <td style="text-align: center; font-size: 13px;">{{ number_format($price) }}원</td>
                                <td style="text-align: center; font-size: 13px; color: #888;">
                                    @if(($pricing['domae_price'] - $price) > 0)
                                        -{{ number_format($pricing['domae_price'] - $price) }}원
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="price_cell price_bold" style="text-align: center; font-size: 13px; font-weight: bold; color: #333;">{{ number_format($itemPrice) }}원</td>
                                
                                <!-- Shipping Info per Row in Legacy -->
                                <td style="text-align: center; font-size: 12px; border-left: 1px solid #eee;">
                                    @if($group['is_postpaid'])
                                        <span style="color:red; font-weight:bold;">본사<br>착불</span>
                                    @else
                                        <span class="shipping-cost-display">본사<br>택배(선불)<br>{{ $group['shipping_cost'] > 0 ? number_format($group['shipping_cost']) . '원' : '무료' }}</span>
                                        <button type="button" class="op_change_btn_legacy" style="display:block; margin:4px auto 0;" onclick="alert('주문서 작성 단계에서 배송지 변경이 가능합니다.');">변경</button>
                                        @if($packagingCost > 0)
                                            <div style="font-size: 11px; color: #888; margin-top: 3px;">+ {{ number_format($packagingCost) }}원</div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <!-- Should not happen within a valid group, but good practice -->
                        @endforelse
                    </tbody>
                    @empty
                    <tbody>
                        <tr>
                            <td colspan="7" class="no_data" style="text-align: center; padding: 40px; font-size: 14px; color: #777;">장바구니에 담긴 상품이 없습니다.</td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>

                <!-- Shipping Detail info block at right -->
                @if(!$groupedCart == [])
                    <div class="shipping_detail_info" style="text-align: right; font-size: 12px; color: #888; margin-top: 8px; font-family: '맑은고딕', sans-serif;">
                        추가배송비 : <span style="font-weight:bold;">{{ number_format($packagingCost) }}원</span> &nbsp; 기본배송비 : <span style="font-weight:bold;">{{ number_format($baseShipping) }}원</span>
                    </div>
                @endif

                <!-- Lower buttons: Legacy layout -->
                <div class="cart_button_wrap" style="margin-top: 12px; display: flex; justify-content: flex-start; gap: 6px; align-items: center;">
                    <button type="button" class="btn_chk_all_toggle" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 2px; cursor: pointer; color:#333; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">상품 전체 선택/해제</button>
                    <button type="button" class="btn_select_del" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 2px; cursor: pointer; color:#333; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">선택삭제</button>
                    @if(config('shop.shipping.useestimate', 'Y') === 'Y')
                        <button type="button" class="btn_print_estimate" style="height: 32px; padding: 0 16px; background: #2b77f3; border: 1px solid #2b77f3; font-size: 13px; border-radius: 2px; cursor: pointer; color:#fff; font-weight:bold; transition: all 0.2s;" onmouseover="this.style.background='#1557c0'" onmouseout="this.style.background='#2b77f3'" onclick="window.open('/prints/form_print_estimate?code=cart', '_estimate', 'width=960,height=640,scrollbar=1');">견적서</button>
                    @endif
                </div>

                <!-- Legacy styled Summary Total Bar -->
                <div class="cart_total_area_v2">
                    <div class="summary_left" style="width: 150px; border-right: 1px solid #e0e2e4; padding-right: 15px; font-family: '맑은고딕', sans-serif;">
                        <div style="margin-bottom: 6px; color: #555; font-size: 13px;">총 상품: <strong id="total_goods_count" style="color: #333; font-weight: bold; font-size: 14px;">0</strong></div>
                        <div style="color: #555; font-size: 13px;">총 수량: <strong id="total_goods_qty" style="color: #333; font-weight: bold; font-size: 14px;">0</strong></div>
                    </div>
                    <div class="summary_right" style="flex: 1; display: flex; justify-content: space-around; align-items: center; padding-left: 20px; font-family: '맑은고딕', sans-serif; color: #555; font-size: 13px;">
                        <div>총 상품 금액: <strong id="total_goods_price" style="font-size: 16px; color: #333; font-weight: bold;">{{ number_format($totalPrice) }}원</strong></div>
                        <span class="circle-op">+</span>
                        <div>배송비: <strong id="total_delivery_price" style="font-size: 16px; color: #333; font-weight: bold;">0원</strong></div>
                        <span class="circle-op">-</span>
                        <div>총 할인: <strong id="total_discount_price" style="font-size: 16px; color: #333; font-weight: bold;">0원</strong></div>
                        <span class="circle-op">+</span>
                        <div>총 부가세: <strong id="total_tax_price" style="font-size: 16px; color: #333; font-weight: bold;">{{ number_format($totalVat) }}원</strong></div>
                        <div>예상포인트: <strong id="total_point_price" style="font-size: 16px; color: #333; font-weight: bold;">{{ number_format($totalPoint ?? 0) }}원</strong></div>
                        <span class="circle-op">=</span>
                        <div style="color: #333;">총 결제금액: <strong id="total_settle_price" style="font-size: 24px; color: #d32f2f; font-weight: bold; margin-left: 5px;">0원</strong></div>
                    </div>
                </div>

                <div class="btn_area_center" style="margin-top: 30px; display: flex; justify-content: center; gap: 10px;">
                    <button type="button" class="btn_order_all" style="height: 50px; padding: 0 32px; background: #d32f2f; color: #fff; font-size: 16px; font-weight: bold; border: 1px solid #d32f2f; border-radius: 2px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#b71c1c'" onmouseout="this.style.background='#d32f2f'">전체상품주문</button>
                    <button type="button" class="btn_order_select" style="height: 50px; padding: 0 32px; background: #555; color: #fff; font-size: 16px; font-weight: bold; border: 1px solid #555; border-radius: 2px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#555'">선택상품주문</button>
                    <button type="button" class="btn_continue_shopping" style="height: 50px; padding: 0 32px; background: #fff; color: #333; font-size: 16px; font-weight: bold; border: 1px solid #ccc; border-radius: 2px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'" onclick="location.href='/';">계속 쇼핑하기</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="modal_container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check All
            const chkAll = document.getElementById('chk_all');
            const chkItems = document.querySelectorAll('.chk_item');

            if (chkAll) {
                chkAll.addEventListener('change', function () {
                    chkItems.forEach(chk => {
                        chk.checked = this.checked;
                    });
                    calcTotal();
                });
            }

            chkItems.forEach(chk => {
                chk.addEventListener('change', function () {
                    if (chkAll) {
                        const checkedCount = document.querySelectorAll('.chk_item:checked').length;
                        chkAll.checked = (checkedCount === chkItems.length);
                    }
                    calcTotal();
                });
            });

            // Toggle All Button Events (Upper and Lower)
            document.querySelectorAll('.btn_chk_all_toggle').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (chkAll) {
                        chkAll.checked = !chkAll.checked;
                        chkItems.forEach(chk => {
                            chk.checked = chkAll.checked;
                        });
                        calcTotal();
                    }
                });
            });

            // Quantity Update
            document.querySelectorAll('.btn_qty_mod').forEach(btn => {
                btn.addEventListener('click', function () {
                    const tr = this.closest('tr');
                    const cartSeq = tr.dataset.cartSeq;
                    const ea = tr.querySelector('.qty_input').value;

                    if (ea < 1) {
                        alert('수량은 1개 이상이어야 합니다.');
                        return;
                    }

                    fetch('{{ route("cart.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ cart_seq: cartSeq, ea: ea })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Update row price with NEW unit price from server (reflects tiered discount)
                                if (data.new_unit_price) {
                                    tr.dataset.price = data.new_unit_price;
                                    const itemPrice = data.new_total_price;
                                    tr.querySelector('.price_cell').innerText = new Intl.NumberFormat().format(itemPrice) + '원';
                                    // Also update unit price display if exists? (Currently only total is shown in UI)
                                    // table cell 5 is unit price text
                                    tr.children[5].innerText = new Intl.NumberFormat().format(data.new_unit_price) + '원';
                                } else {
                                    // Fallback (shouldn't happen with updated controller)
                                    const price = parseInt(tr.dataset.price);
                                    const itemPrice = price * ea;
                                    tr.querySelector('.price_cell').innerText = new Intl.NumberFormat().format(itemPrice) + '원';
                                }
                                
                                calcTotal();
                            } else {
                                alert(data.message);
                            }
                        });
                });
            });

            // Option Update Modal
            document.querySelectorAll('.btn_opt_mod').forEach(btn => {
                btn.addEventListener('click', function () {
                    const tr = this.closest('tr');
                    const cartSeq = tr.dataset.cartSeq;

                    fetch(`{{ route('cart.optionalChanges') }}?cart_seq=${cartSeq}`)
                        .then(res => res.text())
                        .then(html => {
                            const modalContainer = document.getElementById('modal_container');
                            modalContainer.innerHTML = html;
                            
                            const modal = document.getElementById('modal_optional_changes');
                            const dimmed = document.querySelector('.modal_dimmed');
                            
                            modal.style.display = 'block';
                            dimmed.style.display = 'block';

                            // Close events
                            document.querySelectorAll('.btn_close_modal, .modal_dimmed').forEach(closeBtn => {
                                closeBtn.addEventListener('click', function() {
                                    modalContainer.innerHTML = '';
                                });
                            });

                            // Save event
                            document.getElementById('btn_save_optional_changes').addEventListener('click', function() {
                                const newOptionSeq = document.getElementById('new_option_seq').value;
                                if (!newOptionSeq) {
                                    alert('변경할 옵션을 선택하세요.');
                                    return;
                                }

                                fetch('{{ route("cart.changeOption") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ cart_seq: cartSeq, option_seq: newOptionSeq })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if(data.status === 'success') {
                                        location.reload();
                                    } else {
                                        alert(data.message);
                                    }
                                });
                            });
                        });
                });
            });

            // Delete Item
            document.querySelectorAll('.btn_del').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (!confirm('삭제하시겠습니까?')) return;

                    const tr = this.closest('tr');
                    const cartSeq = tr.dataset.cartSeq;

                    fetch('{{ route("cart.destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ cart_seq: [cartSeq] })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                tr.remove();
                                calcTotal();
                            } else {
                                alert(data.message);
                            }
                        });
                });
            });

            // Calculate Total
            function calcTotal() {
                let grandTotalGoods = 0; // All items goods total
                let grandTotalDelivery = 0; // All delivery total
                let totalTax = 0; // Taxable items tax
                let totalPoint = 0; // Accumulated point
                let checkedCount = 0; // Number of distinct goods checked
                let checkedQty = 0; // Sum of quantities of checked items

                const freeThreshold = {{ $freeShippingThreshold }};
                const shippingCost = {{ $baseShipping }}; // Synced with base shipping from config
                const packagingCost = {{ $packagingCost }};

                // Iterate over each Shipping Group (tbody)
                document.querySelectorAll('tbody.shipping-group').forEach(tbody => {
                    let groupTotalGoods = 0;
                    let hasPostpaid = false;
                    let hasCheckedItems = false;

                    // Calculate items within this group
                    tbody.querySelectorAll('.chk_item:checked').forEach(chk => {
                        hasCheckedItems = true;
                        checkedCount++;
                        const tr = chk.closest('tr');
                        const price = parseInt(tr.dataset.price);
                        const ea = parseInt(tr.querySelector('.qty_input').value);
                        const isPostpaid = tr.dataset.isPostpaid === "1";
                        const taxType = tr.dataset.tax;
                        const point = parseInt(tr.dataset.point) || 0;
                        
                        const itemTotal = price * ea;
                        groupTotalGoods += itemTotal;
                        grandTotalGoods += itemTotal;
                        totalPoint += point * ea;
                        checkedQty += ea;

                        if (taxType === 'tax') {
                            totalTax += Math.floor(itemTotal * 0.1);
                        }

                        if (isPostpaid) {
                            hasPostpaid = true;
                        }
                    });

                    // Calculate Delivery for this group
                    let groupDelivery = 0;
                    if (hasCheckedItems && !hasPostpaid) {
                        if (groupTotalGoods < freeThreshold) {
                            groupDelivery = shippingCost; // Synced dynamically
                        }
                    }
                    grandTotalDelivery += groupDelivery;
                });

                let finalPackaging = 0;
                if (grandTotalGoods > 0) {
                    finalPackaging = packagingCost;
                }

                // Apply 10-won cut-off to Tax
                totalTax = Math.floor(totalTax / 10) * 10;

                const finalDelivery = grandTotalDelivery + finalPackaging;
                let final = grandTotalGoods + finalDelivery + totalTax;
                
                // Apply 10-won cut-off to Final Price
                final = Math.floor(final / 10) * 10;

                document.getElementById('total_goods_count').innerText = new Intl.NumberFormat().format(checkedCount);
                document.getElementById('total_goods_qty').innerText = new Intl.NumberFormat().format(checkedQty);
                document.getElementById('total_goods_price').innerText = new Intl.NumberFormat().format(grandTotalGoods) + '원';
                document.getElementById('total_delivery_price').innerText = new Intl.NumberFormat().format(finalDelivery) + '원';
                document.getElementById('total_tax_price').innerText = new Intl.NumberFormat().format(totalTax) + '원';
                document.getElementById('total_point_price').innerText = new Intl.NumberFormat().format(totalPoint) + '원';
                document.getElementById('total_settle_price').innerText = new Intl.NumberFormat().format(final) + '원';
            }

            // Order Buttons
            document.querySelector('.btn_order_all').addEventListener('click', function () {
                const chkItems = document.querySelectorAll('.chk_item');
                if (chkItems.length === 0) {
                    alert('장바구니에 담긴 상품이 없습니다.');
                    return;
                }

                // Check all items
                chkItems.forEach(chk => chk.checked = true);
                if (document.getElementById('chk_all')) document.getElementById('chk_all').checked = true;

                const form = document.getElementById('cartForm');
                form.action = "{{ route('order.form') }}";
                form.submit();
            });

            document.querySelector('.btn_order_select').addEventListener('click', function () {
                const selected = document.querySelectorAll('.chk_item:checked');
                if (selected.length === 0) {
                    alert('선택된 상품이 없습니다.');
                    return;
                }
                const form = document.getElementById('cartForm');
                form.action = "{{ route('order.form') }}";
                form.submit();
            });

            // Selection Delete Items
            document.querySelectorAll('.btn_select_del').forEach(btn => {
                btn.addEventListener('click', function () {
                    const selected = document.querySelectorAll('.chk_item:checked');
                    if (selected.length === 0) {
                        alert('삭제할 상품을 선택해 주세요.');
                        return;
                    }

                    if (!confirm('선택한 상품을 삭제하시겠습니까?')) return;

                    const cartSeqs = Array.from(selected).map(chk => chk.value);

                    fetch('{{ route("cart.destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ cart_seq: cartSeqs })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                selected.forEach(chk => {
                                    chk.closest('tr').remove();
                                });
                                calcTotal();
                            } else {
                                alert(data.message);
                            }
                        });
                });
            });

            // Run calculation on initial load
            calcTotal();
        });
    </script>

@endsection