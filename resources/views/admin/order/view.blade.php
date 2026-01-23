@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Order Header --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">주문 상세 정보 [{{ $order->order_seq }}]</h3>
                <div class="card-tools">
                    @if($order->step == 15)
                        <button class="btn btn-sm btn-success" onclick="updateStatus('deposit_confirm')">입금확인</button>
                    @endif
                    @if($order->step == 25)
                        <button class="btn btn-sm btn-info" onclick="updateStatus('prepare_goods')">상품준비</button>
                    @endif
                    @if($order->step == 45)
                        <button class="btn btn-sm btn-primary" onclick="updateStatus('export_goods')">배송처리</button>
                    @endif
                    
                    @if($order->step < 95)
                        <button class="btn btn-sm btn-danger ml-2" onclick="cancelOrder()">주문취소</button>
                    @endif
                    
                    <span class="badge ml-2" style="background-color: {{ \App\Models\Order::getStepColor($order->step) }}; font-size: 1rem;">
                        {{ \App\Models\Order::getStepName($order->step) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>주문번호</th>
                        <td>{{ $order->order_seq }}</td>
                        <th>주문일시</th>
                        <td>{{ $order->regist_date }}</td>
                    </tr>
                    <tr>
                        <th>주문자</th>
                        <td>
                            @if($order->member)
                                {{ $order->member->user_name }} ({{ $order->member->userid }})
                            @else
                                {{ $order->order_user_name }} (비회원)
                            @endif
                        </td>
                        <th>회원등급</th>
                        <td>{{ $order->member ? $order->member->group_name : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">주문 상품 목록</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="openProductSearch()">상품추가</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>이미지</th>
                            <th>상품정보</th>
                            <th>수량</th>
                            <th>판매가</th>
                            <th>배송비</th>
                            <th>상태</th>
                            <th>재고</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                @if($item->goods && $item->goods->images->first())
                                    <img src="/data/goods/{{ $item->goods->images->first()->image }}" width="50" height="50">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>
                            <td>
                                <div><a href="/goods/exclude/view/{{ $item->goods_seq }}" target="_blank">{{ $item->goods_name }}</a></div>
                                @foreach($item->options as $opt)
                                    <div class="text-muted small">
                                        [옵션] {{ $opt->title1 }}: {{ $opt->option1 }} 
                                        @if($opt->price != 0) ({{ number_format($opt->price) }}원) @endif
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $item->ea }}</td>
                            <td>{{ number_format($item->price) }}원</td>
                            <td>{{ number_format($item->shipping_cost) }}원</td>
                            <td>
                                <span class="badge" style="background-color: {{ \App\Models\Order::getStepColor($item->step) }}">
                                    {{ \App\Models\Order::getStepName($item->step) }}
                                </span>
                            </td>
                            <td>
                                {{-- Placeholder for Real Stock --}}
                                -
                            </td>
                            <td>
                                @if($item->options->isNotEmpty())
                                <button class="btn btn-xs btn-info" onclick="openReplaceModal('{{ $item->options->first()->item_option_seq }}')">교환</button>
                                @endif
                                <button class="btn btn-xs btn-danger" onclick="cancelItem('{{ $item->item_seq }}')">취소</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shipping & Payment (Simplified) --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">배송 정보</h3></div>
                    <div class="card-body">
                         <div class="form-group row">
                            <label class="col-sm-3 col-form-label">받는사람</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" value="{{ $order->recipient_user_name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">주소</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control mb-1" value="{{ $order->recipient_zipcode }}">
                                <input type="text" class="form-control" value="{{ $order->recipient_address }}">
                                <input type="text" class="form-control mt-1" value="{{ $order->recipient_address_detail }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">결제 정보</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="openPriceModal()">
                                <i class="fas fa-edit"></i> 수정
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                             <tr><th>결제방법</th><td class="text-right">{{ $order->payment }}</td></tr>
                             <tr><th>상품합계</th><td class="text-right">{{ number_format($order->summ_price) }}원</td></tr>
                             <tr><th>배송비</th><td class="text-right">+ {{ number_format($order->shipping_cost) }}원</td></tr>
                             @if($order->coupon_sale > 0)
                             <tr><th>쿠폰할인</th><td class="text-right text-danger">- {{ number_format($order->coupon_sale) }}원</td></tr>
                             @endif
                             @if($order->emoney > 0)
                             <tr><th>적립금사용</th><td class="text-right text-danger">- {{ number_format($order->emoney) }}원</td></tr>
                             @endif
                             <tr class="font-weight-bold border-top">
                                <th>최종결제금액</th>
                                <td class="text-right">{{ number_format($order->settleprice) }}원</td>
                             </tr>
                             <tr><th>입금자명</th><td class="text-right">{{ $order->depositor }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODALS --}}
@include('admin.order.partials.replace_modal')
@include('admin.order.partials.price_modal')

@endsection

@section('custom_js')
<script>
    function openReplaceModal(itemSeq) {
        $('#replaceModal').modal('show');
        $('#replace_original_item_seq').val(itemSeq);
    }

    function openProductSearch() {
        alert('상품 검색 기능은 준비중입니다.');
    }

    function cancelItem(itemSeq) {
        if(!confirm('이 상품을 취소하시겠습니까?')) return;
        alert('준비중입니다.');
    }

    function updateStatus(mode) {
        let msg = "상태를 변경하시겠습니까?";
        if(mode == 'deposit_confirm') msg = "입금 확인 처리를 하시겠습니까? (주문접수 -> 결제확인)";
        if(mode == 'prepare_goods') msg = "상품 준비중 처리를 하시겠습니까? (결제확인 -> 상품준비)";
        if(mode == 'export_goods') msg = "배송 처리를 하시겠습니까? (상품준비 -> 배송처리)"; 
        if(mode == 'cancel_order') msg = "주문을 취소하시겠습니까? (복구 불가)";

        if(!confirm(msg)) return;

        $.ajax({
            url: "{{ route('admin.order.process') }}",
            type: "POST",
            data: {
                order_seq: "{{ $order->order_seq }}",
                mode: mode
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.success) {
                    alert('처리되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + res.message);
                }
            },
            error: function(err) {
                alert('서버 오류가 발생했습니다.');
            }
        });
    }

    function cancelOrder() {
        updateStatus('cancel_order');
    }

    // --- Price Edit Modal Functions ---
    function openPriceModal() {
        $('#priceModal').modal('show');
    }

    function savePrice() {
        if(!confirm('가격을 수정하시겠습니까?')) return;

        const formData = {
            order_seq: '{{ $order->order_seq }}',
            change_type: 'price_info',
            settleprice: $('#modal_settleprice').val(),
            emoney: $('#modal_emoney').val(),
            shipping_cost: $('#modal_shipping_cost').val(),
            coupon_sale: $('#modal_coupon_sale').val(),
            admin_memo: $('#modal_admin_memo').val()
        };

        $.ajax({
            url: "{{ route('admin.order.update_price') }}",
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.success) {
                    alert('수정되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + res.message);
                }
            },
            error: function(err) {
                alert('서버 오류가 발생했습니다: ' + err.statusText);
            }
        });
    }
</script>
@endsection

{{-- Price Edit Modal --}}
<div class="modal fade" id="priceModal" tabindex="-1" role="dialog" aria-labelledby="priceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priceModalLabel">주문 금액 수정</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="priceForm">
                    <div class="form-group">
                        <label>결제금액 (실제 PG승인금액)</label>
                        <input type="number" class="form-control" id="modal_settleprice" value="{{ $order->settleprice }}">
                    </div>
                    <div class="form-group">
                        <label>적립금 사용</label>
                        <input type="number" class="form-control" id="modal_emoney" value="{{ $order->emoney }}">
                    </div>
                    <div class="form-group">
                        <label>배송비</label>
                        <input type="number" class="form-control" id="modal_shipping_cost" value="{{ $order->shipping_cost }}">
                    </div>
                    <div class="form-group">
                        <label>쿠폰 할인</label>
                        <input type="number" class="form-control" id="modal_coupon_sale" value="{{ $order->coupon_sale }}">
                    </div>
                    <div class="form-group">
                        <label>관리자 메모 (수정 사유)</label>
                        <textarea class="form-control" id="modal_admin_memo" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" onclick="savePrice()">저장</button>
            </div>
        </div>
    </div>
</div>

@include('admin.order.partials.replace_modal')

@section('custom_js')
<script>
    function openReplaceModal(itemSeq) {
        $('#replaceModal').modal('show');
        $('#replace_original_item_seq').val(itemSeq);
    }

    function openPriceModal(itemSeq, price, emoney, coupon, memo) {
        $('#modal_order_seq').val('{{ $order->order_seq }}');
        $('#modal_settle_price').val(price);
        $('#modal_emoney').val(emoney);
        $('#modal_coupon_sale').val(coupon);
        $('#modal_admin_memo').val(memo);
        $('#priceModal').modal('show');
    }

    function savePrice() {
        var orderSeq = $('#modal_order_seq').val();
        var price = $('#modal_settle_price').val();
        var emoney = $('#modal_emoney').val();
        var coupon = $('#modal_coupon_sale').val();
        var memo = $('#modal_admin_memo').val();
        
        $.ajax({
            url: '{{ route("admin.order.update_price") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_seq: orderSeq,
                settle_price: price,
                emoney: emoney,
                coupon_sale: coupon,
                admin_memo: memo
            },
            success: function(response) {
                if (response.success) {
                    alert('가격 정보가 수정되었습니다.');
                    location.reload();
                } else {
                    alert('오류가 발생했습니다: ' + response.message);
                }
            },
            error: function() {
                alert('서버 오류가 발생했습니다.');
            }
        });
    }
</script>
@endsection
