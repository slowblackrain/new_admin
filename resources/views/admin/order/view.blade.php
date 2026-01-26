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
                    <div class="card-header">
                        <h3 class="card-title">배송 정보</h3>
                        <div class="card-tools">
                             <button type="button" class="btn btn-tool" onclick="updateRecipientInfo()">
                                <i class="fas fa-save"></i> 저장
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                         <form id="recipientForm">
                             <div class="form-group row">
                                <label class="col-sm-3 col-form-label">받는사람</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="recipient_user_name" value="{{ $order->recipient_user_name }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">연락처</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="recipient_phone" value="{{ $order->recipient_phone }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">주소</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control mb-1" name="recipient_zipcode" value="{{ $order->recipient_zipcode }}">
                                    <input type="text" class="form-control" name="recipient_address" value="{{ $order->recipient_address }}">
                                    <input type="text" class="form-control mt-1" name="recipient_address_detail" value="{{ $order->recipient_address_detail }}">
                                </div>
                            </div>
                        </form>
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

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">처리 이력 (Order Log)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>일시</th>
                            <th>구분</th>
                            <th>작업자</th>
                            <th>제목</th>
                            <th>상세내용</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->logs as $log)
                        <tr>
                            <td>{{ $log->regist_date->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->type }}</td>
                            <td>{{ $log->actor }}</td>
                            <td>{{ $log->title }}</td>
                            <td>{{ $log->detail }}</td>
                        </tr>
                        @endforeach
                        @if($order->logs->isEmpty())
                        <tr><td colspan="5" class="text-center">이력이 없습니다.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.order.partials.replace_modal')
@include('admin.order.partials.price_modal')

@endsection

@section('custom_js')
<script>
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


    function updateRecipientInfo() {
        if(!confirm('배송 정보를 수정하시겠습니까?')) return;
        
        const data = $('#recipientForm').serializeArray();
        data.push({name: 'order_seq', value: '{{ $order->order_seq }}'});
        
        $.ajax({
            url: "{{ route('admin.order.update_recipient') }}",
            type: "POST",
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if(res.success) {
                    alert('수정되었습니다.');
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
</script>
@endsection

