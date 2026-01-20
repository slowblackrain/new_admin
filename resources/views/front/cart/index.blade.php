@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 장바구니</em>
        </div>
    </div>

    <div class="content_wrap">
        <div class="cart_title_area">
            <h3>장바구니</h3>
        </div>

        <div class="cart_list_area">
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
                    <tbody>
                        @php $totalPrice = 0; @endphp
                        @forelse($cartItems as $item)
                            @php
                                $goods = $item->goods;
                                $option = $item->options->first();
                                
                                // Matches the correct option from Goods based on the saved strings
                                // This ensures we get the real current price
                                $price = 0;
                                $matchedOption = null;
                                
                                if ($goods && $goods->option) {
                                    $matchedOption = $goods->option->first(function($o) use ($option) {
                                         return (string)$o->option1 == (string)$option->option1 &&
                                                (string)$o->option2 == (string)$option->option2 &&
                                                (string)$o->option3 == (string)$option->option3 &&
                                                (string)$o->option4 == (string)$option->option4 &&
                                                (string)$o->option5 == (string)$option->option5;
                                    });
                                }
                                
                                if ($matchedOption) {
                                    $price = $matchedOption->price;
                                } else {
                                    // Fallback: If option not found (data changed), use first option price or 0
                                    $price = $goods->option->first()->price ?? 0;
                                }

                                $optionParts = [];
                                if ($option->option1) $optionParts[] = $option->option1;
                                if ($option->option2) $optionParts[] = $option->option2;
                                if ($option->option3) $optionParts[] = $option->option3;
                                if ($option->option4) $optionParts[] = $option->option4;
                                if ($option->option5) $optionParts[] = $option->option5;
                                $optionStr = implode(' / ', $optionParts);

                                $ea = $option->ea ?? 1;
                                $itemPrice = $price * $ea;
                                $totalPrice += $itemPrice;

                                $mainImage = $goods->images->where('image_type', 'thumbCart')->first() 
                                    ?? $goods->images->where('image_type', 'list1')->first()
                                    ?? $goods->images->where('image_type', 'view')->first();
                                
                                $imagePath = $mainImage ? $mainImage->image : '';
                                if ($imagePath && strpos($imagePath, '/data/goods/') === 0) {
                                    $imgSrc = $imagePath;
                                } elseif ($imagePath) {
                                    $imgSrc = '/data/goods/' . $imagePath;
                                } else {
                                    $imgSrc = '/images/no_image.gif';
                                }
                            @endphp
                            <tr data-cart-seq="{{ $item->cart_seq }}" data-price="{{ $price }}">
                                <td><input type="checkbox" name="cart_seq[]" class="chk_item" value="{{ $item->cart_seq }}"
                                        checked></td>
                                <td class="img_cell">
                                    <a href="{{ route('goods.view', ['no' => $goods->goods_seq]) }}">
                                        <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name }}" width="60" onerror="this.src='/images/no_image.gif'">
                                    </a>
                                </td>
                                <td class="info_cell">
                                    <div class="g_name">{{ $goods->goods_name }}</div>
                                    @if($optionStr && $optionStr != '기본')
                                        <div class="g_opt">옵션: {{ $optionStr }}</div>
                                    @endif

                                    {{-- Input Fields Display --}}
                                    @if($item->inputs->count() > 0)
                                        <div class="g_inputs display_inputs_area">
                                            @foreach($item->inputs as $input)
                                                <div class="input_row">
                                                    <span class="input_badge">[입력]</span>
                                                    <strong>{{ $input->input_title }}:</strong>
                                                    @if($input->type == 'file')
                                                        @php
                                                            // Ensure path is relative to storage root for asset()
                                                            // Laravel storeAs returns path relative to disk root.
                                                            // asset('storage/...') maps to public/storage -> storage/app/public
                                                            $fileUrl = asset('storage/' . $input->input_value);
                                                            $fileName = basename($input->input_value);
                                                        @endphp
                                                        <a href="{{ $fileUrl }}" target="_blank" class="file_link">
                                                            {{ $fileName }} (확인)
                                                        </a>
                                                    @else
                                                        {{ $input->input_value }}
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" name="ea" value="{{ $ea }}" min="1" class="qty_input"
                                        style="width: 50px;">
                                    <button type="button" class="btn_qty_mod">변경</button>
                                </td>
                                <td>{{ number_format($price) }}원</td>
                                <td>기본배송</td>
                                <td class="price_cell price_bold">{{ number_format($itemPrice) }}원</td>
                                <td>
                                    <button type="button" class="btn_del">삭제</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no_data">장바구니에 담긴 상품이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
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
                                id="total_tax_price">{{ number_format(floor($totalPrice * 0.1)) }}원</strong></span>
                        <span class="equal">=</span>
                        <span class="final_price">총 결제금액 <strong
                                id="total_settle_price">{{ number_format($totalPrice + floor($totalPrice * 0.1)) }}원</strong></span>
                    </div>
                </div>

                <div class="btn_area_center">
                    <button type="button" class="btn_order_all">전체상품주문</button>
                    <button type="button" class="btn_order_select">선택상품주문</button>
                </div>
            </form>
        </div>
    </div>

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
                                // Update row price
                                const price = parseInt(tr.dataset.price);
                                const itemPrice = price * ea;
                                tr.querySelector('.price_cell').innerText = new Intl.NumberFormat().format(itemPrice) + '원';
                                calcTotal();
                                alert(data.message);
                            } else {
                                alert(data.message);
                            }
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
                let total = 0;
                document.querySelectorAll('.chk_item:checked').forEach(chk => {
                    const tr = chk.closest('tr');
                    const price = parseInt(tr.dataset.price);
                    const ea = parseInt(tr.querySelector('.qty_input').value);
                    total += price * ea;
                });

                const shippingCost = {{ $shippingCost }};
                const freeThreshold = {{ $freeShippingThreshold }};
                const packagingCost = {{ $packagingCost }};

                const tax = Math.floor(total * 0.1);
                let delivery = 0;
                
                if (total > 0 && total < freeThreshold) {
                    delivery = shippingCost;
                }

                // Packaging cost is always added if there are items
                let finalPackaging = 0;
                if (total > 0) {
                    finalPackaging = packagingCost;
                }

                const final = total + delivery + tax + finalPackaging;

                document.getElementById('total_goods_price').innerText = new Intl.NumberFormat().format(total) + '원';
                document.getElementById('total_delivery_price').innerText = new Intl.NumberFormat().format(delivery) + '원';
                document.getElementById('total_tax_price').innerText = new Intl.NumberFormat().format(tax) + '원';
                document.getElementById('total_packaging_price').innerText = new Intl.NumberFormat().format(finalPackaging) + '원';
                document.getElementById('total_settle_price').innerText = new Intl.NumberFormat().format(final) + '원';
            }

            // Order Buttons (Placeholder)
            document.querySelector('.btn_order_all').addEventListener('click', function () {
                alert('주문 기능은 아직 구현되지 않았습니다.');
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

            // Run calculation on initial load
            calcTotal();
        });
    </script>

    <style>
        .cart_table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #333;
        }

        .cart_table th {
            background: #f9f9f9;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .cart_table td {
            padding: 15px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .cart_table .info_cell {
            text-align: left;
        }

        .cart_table .g_name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .cart_table .g_opt {
            font-size: 12px;
            color: #888;
        }

        .price_bold {
            font-weight: bold;
            color: #d00;
        }

        .cart_total_area {
            background: #f2f2f2;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .total_box span {
            font-size: 16px;
            margin: 0 10px;
        }

        .total_box strong {
            font-size: 20px;
            font-weight: bold;
        }

        .total_box .final_price strong {
            color: #d00;
        }

        .btn_area_center {
            margin-top: 30px;
            text-align: center;
        }

        .btn_area_center button {
            padding: 15px 40px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn_order_all {
            background: #d00;
            color: #fff;
        }

        .btn_order_select {
            background: #333;
            color: #fff;
            margin-left: 10px;
        }

        .no_data {
            padding: 50px 0;
            color: #888;
        }

        .g_inputs {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 4px;
        }

        .input_row {
            margin-bottom: 2px;
        }

        .input_badge {
            display: inline-block;
            background: #eee;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 11px;
            margin-right: 4px;
            color: #555;
        }

        .file_link {
            color: #007bff;
            text-decoration: underline;
        }

        .btn_qty_mod {
            padding: 2px 5px;
            font-size: 11px;
            cursor: pointer;
        }
    </style>
@endsection