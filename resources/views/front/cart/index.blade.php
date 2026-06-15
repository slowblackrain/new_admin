@extends('layouts.front')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="/css/order.css?v={{ time() }}">
    @endpush
    <div class="order_header_v2">
        <div class="order_header_inner clearbox">
            <!-- Left: Title with Icon -->
            <div class="title_area">
                <h2>장바구니<i><img src="/images/icon/order_card.png" alt="Cart Icon"></i></h2>
            </div>
            
            <!-- Right: Step Indicator -->
            <div class="step_area">
                <ul>
                    <li class="on"><span class="num">1</span> <span class="txt">장바구니</span></li>
                    <li><span class="num">2</span> <span class="txt">주문/결제</span></li>
                    <li><span class="num">3</span> <span class="txt">주문완료</span></li>
                </ul>
            </div>
        </div>
    </div>

        <div class="cart_list_area">
            <div class="cart_button_wrap" style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" class="btn_select_del" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 3px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'">선택 삭제</button>
                </div>
                <div>
                    @if(config('shop.shipping.useestimate', 'Y') === 'Y')
                        <button type="button" class="btn_print_estimate" style="height: 32px; padding: 0 12px; background: #fff; border: 1px solid #ccc; font-size: 13px; border-radius: 3px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'" onclick="window.open('/prints/form_print_estimate?code=cart', '_estimate', 'width=960,height=640,scrollbar=1');">
                            <i class="fas fa-print" style="margin-right: 4px;"></i> 견적서 인쇄
                        </button>
                    @endif
                </div>
            </div>

            <form name="cartForm" id="cartForm" method="post" action="">
                @csrf

                <table class="cart_table">
                    <colgroup>
                        <col width="40" />
                        <col width="100" />
                        <col width="*" />
                        <col width="100" />
                        <col width="100" />
                        <col width="100" />
                        <col width="100" />
                        <col width="100" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" id="chk_all" checked></th>
                            <th scope="col">이미지</th>
                            <th scope="col">상품정보</th>
                            <th scope="col">수량</th>
                            <th scope="col">상품금액</th>
                            <th scope="col">배송비</th>
                            <th scope="col">합계</th>
                            <th scope="col">선택</th>
                        </tr>
                    </thead>
                    @php
                        $totalPrice = 0;
                        $totalVat = $totalVat ?? 0;
                    @endphp
                    @forelse($groupedCart as $groupKey => $group)
                    <tbody class="shipping-group" data-group-key="{{ $groupKey }}">
                        <tr class="group-header" style="background-color: #f8f9fa;">
                            <td colspan="8" style="text-align: left; padding: 10px 15px; font-weight: bold; font-size: 14px; border-bottom: 2px solid #ddd;">
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
                            <tr data-cart-seq="{{ $item->cart_seq }}" data-price="{{ $price }}" data-is-postpaid="{{ $item->is_postpaid ? 1 : 0 }}" data-tax="{{ $goods->tax ?? 'tax' }}" data-group-key="{{ $groupKey }}">
                                <td><input type="checkbox" name="cart_seq[]" class="chk_item" value="{{ $item->cart_seq }}" checked></td>
                                <td class="img_cell">
                                    <a href="{{ route('goods.view', ['no' => $goods->goods_seq]) }}">
                                        <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name }}" width="60" onerror="this.src='/images/no_image.gif'">
                                    </a>
                                </td>
                                <td class="info_cell">
                                    <div class="g_name">
                                        {{ $goods->goods_name }}
                                        @if($item->is_postpaid)
                                            <span class="badge_postpaid" style="color:red; font-size:11px; border:1px solid red; padding:0 2px;">[착불]</span>
                                        @endif
                                    </div>
                                    @if($optionStr && $optionStr != '기본')
                                        <div class="g_opt">옵션: {{ $optionStr }}</div>
                                    @endif

                                    {{-- Input Fields Display --}}
                                    @if($item->inputs->count() > 0)
                                        <div class="g_inputs display_inputs_area" style="margin-top: 8px; font-size: 13px; line-height: 1.6;">
                                            @foreach($item->inputs as $input)
                                                <div class="input_row" style="display: flex; align-items: center; margin-bottom: 4px;">
                                                    <i class="fas fa-edit" style="color: #9eabbb; margin-right: 6px; font-size: 12px;"></i>
                                                    <strong style="color: #9eabbb; font-weight: bold; margin-right: 5px;">{{ $input->input_title }}:</strong>
                                                    @if($input->type == 'file')
                                                        @php
                                                            $fileUrl = asset('storage/' . $input->input_value);
                                                            $fileName = basename($input->input_value);
                                                            $isImage = in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                        @endphp
                                                        <a href="{{ $fileUrl }}" target="_blank" class="file_link" style="display: inline-flex; align-items: center; text-decoration: underline; color: #2b77f3;">
                                                            @if($isImage)
                                                                <img src="{{ $fileUrl }}" alt="인쇄이미지" style="width: 18px; height: 18px; object-fit: cover; border: 1px solid #ddd; margin-right: 5px; border-radius: 2px;">
                                                            @endif
                                                            <span style="font-size: 12px;">{{ $fileName }} (확인)</span>
                                                        </a>
                                                    @else
                                                        <span style="color: #333;">{{ $input->input_value }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" name="ea" value="{{ $ea }}" min="1" class="qty_input" style="width: 50px;">
                                    <button type="button" class="btn_qty_mod">변경</button>
                                    @if($options_count ?? 1 > 0)
                                    <br><button type="button" class="btn_opt_mod" style="margin-top:5px; font-size:11px;">옵션/수량변경</button>
                                    @endif
                                </td>
                                <td>{{ number_format($price) }}원</td>
                                
                                {{-- Group Level Shipping Sub-row --}}
                                @if($loop->first)
                                    <td rowspan="{{ count($group['items']) }}" style="border-left: 1px solid #ddd;">
                                        @if($group['is_postpaid'])
                                            <span style="color:red; font-weight:bold;">착불</span>
                                        @else
                                            <span class="shipping-cost-display">{{ $group['shipping_cost'] > 0 ? number_format($group['shipping_cost']) . '원' : '무료' }}</span>
                                            <br><span style="font-size: 11px; color:#666;">(조건부 무료)</span>
                                        @endif
                                    </td>
                                @endif

                                <td class="price_cell price_bold">{{ number_format($itemPrice) }}원</td>
                                <td>
                                    <button type="button" class="btn_del">삭제</button>
                                </td>
                            </tr>
                        @empty
                            <!-- Should not happen within a valid group, but good practice -->
                        @endforelse
                    </tbody>
                    @empty
                    <tbody>
                        <tr>
                            <td colspan="8" class="no_data">장바구니에 담긴 상품이 없습니다.</td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>

                <div class="cart_total_area">
                    <div class="total_box">
                        <span>총 상품금액 <strong id="total_goods_price">{{ number_format($totalPrice) }}원</strong></span>
                        <span class="plus">+</span>
                        <span>배송비 <strong id="total_delivery_price">0원</strong></span>
                        <span class="plus">+</span>
                        <span>포장비 <strong id="total_packaging_price">0원</strong></span>
                        <span class="plus">+</span>
                        <span>부가세 <strong
                                id="total_tax_price">{{ number_format($totalVat) }}원</strong></span>
                        <span class="equal">=</span>
                        <span class="final_price">총 결제금액 <strong
                                id="total_settle_price">{{ number_format($totalPrice + $totalVat) }}원</strong></span>
                    </div>
                </div>

                <div class="btn_area_center">
                    <button type="button" class="btn_order_all">전체상품주문</button>
                    <button type="button" class="btn_order_select">선택상품주문</button>
                    <button type="button" class="btn_continue_shopping" style="height: 50px; padding: 0 24px; background: #fff; color: #333; font-size: 16px; font-weight: bold; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; margin-left: 10px; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='#fff'" onclick="location.href='/';">계속 쇼핑하기</button>
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
                    calcTotal();
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
                                // alert(data.message); // Optional: Remove alert for smoother UX? User kept alert in code.
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
                        const tr = chk.closest('tr');
                        const price = parseInt(tr.dataset.price);
                        const ea = parseInt(tr.querySelector('.qty_input').value);
                        const isPostpaid = tr.dataset.isPostpaid === "1";
                        const taxType = tr.dataset.tax;
                        
                        const itemTotal = price * ea;
                        groupTotalGoods += itemTotal;
                        grandTotalGoods += itemTotal;

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

                    // UI Update for Group Header/Sub-row (Optional sync)
                    // You could update the UI text of the shipping cost cell here if needed
                });

                let finalPackaging = 0;
                if (grandTotalGoods > 0) {
                    finalPackaging = packagingCost;
                }

                const final = grandTotalGoods + grandTotalDelivery + totalTax + finalPackaging;

                document.getElementById('total_goods_price').innerText = new Intl.NumberFormat().format(grandTotalGoods) + '원';
                document.getElementById('total_delivery_price').innerText = new Intl.NumberFormat().format(grandTotalDelivery) + '원';
                document.getElementById('total_tax_price').innerText = new Intl.NumberFormat().format(totalTax) + '원';
                document.getElementById('total_packaging_price').innerText = new Intl.NumberFormat().format(finalPackaging) + '원';
                document.getElementById('total_settle_price').innerText = new Intl.NumberFormat().format(final) + '원';
            }

            // Order Buttons (Placeholder)
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