@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">주문 상세 ({{ $order->order_seq }})</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.order.catalog') }}">주문리스트</a></li>
                        <li class="breadcrumb-item active">주문상세</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">주문 정보</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><th>주문번호</th><td>{{ $order->order_seq }}</td></tr>
                                <tr><th>주문일시</th><td>{{ $order->regist_date }}</td></tr>
                                <tr><th>주문자명</th><td><a href="{{ route('admin.member.view', $order->member_seq) }}">{{ $order->order_user_name }}</a></td></tr>
                                <tr><th>주문자 연락처</th><td>{{ $order->order_cellphone }} / {{ $order->order_phone }}</td></tr>
                                <tr><th>주문자 이메일</th><td>{{ $order->order_email }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">결제/배송 정보</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><th>결제금액</th><td>{{ number_format($order->settleprice) }}원</td></tr>
                                <tr><th>결제방법</th><td>{{ $order->settle_kind }}</td></tr>
                                <tr>
                                    <th>현재상태</th>
                                    <td>
                                        <select id="order_step" class="form-control" style="width: auto; display: inline-block;">
                                            <option value="15" {{ $order->step == 15 ? 'selected' : '' }}>주문접수 (15)</option>
                                            <option value="25" {{ $order->step == 25 ? 'selected' : '' }}>결제확인 (25)</option>
                                            <option value="35" {{ $order->step == 35 ? 'selected' : '' }}>송장출력 (35)</option>
                                            <option value="45" {{ $order->step == 45 ? 'selected' : '' }}>상품준비 (45)</option>
                                            <option value="55" {{ $order->step == 55 ? 'selected' : '' }}>출고완료 (55)</option>
                                            <option value="65" {{ $order->step == 65 ? 'selected' : '' }}>배송중 (65)</option>
                                            <option value="75" {{ $order->step == 75 ? 'selected' : '' }}>배송완료 (75)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr><th>수령자명</th><td>{{ $order->recipient_user_name }}</td></tr>
                                <tr><th>배송지 주소</th><td>({{ $order->recipient_zipcode }}) {{ $order->recipient_address }} {{ $order->recipient_address_detail }}</td></tr>
                                <tr><th>배송메세지</th><td>{{ $order->memo }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">주문 상품 목록</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>이미지</th>
                                <th>상품명</th>
                                <th>옵션</th>
                                <th>수량</th>
                                <th>판매가</th>
                                <th>소계</th>
                                <th>상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @foreach($item->options as $option)
                                <tr>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ $item->image }}" width="50" alt="product">
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->goods_name }}</td>
                                    <td>{{ $option->option1 }}{{ $option->option2 ? ' / '.$option->option2 : '' }}</td>
                                    <td>{{ $option->ea }}</td>
                                    <td>{{ number_format($option->price) }}원</td>
                                    <td>{{ number_format($option->price * $option->ea) }}원</td>
                                    <td>{{ $option->step }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row no-print">
                <div class="col-12">
                    <a href="{{ route('admin.order.catalog') }}" class="btn btn-secondary">목록으로</a>
                    <button type="button" class="btn btn-success float-right" onclick="saveStatus()">
                        <i class="far fa-credit-card"></i> 상태 변경 저장
                    </button>
                    <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
                        <i class="fas fa-download"></i> 거래명세서 출력
                    </button>
                </div>
            </div>
            <br>
        </div>
    </div>

    <script>
    function saveStatus() {
        if (!confirm('주문 상태를 변경하시겠습니까?')) return;
        
        const step = document.getElementById('order_step').value;
        const orderSeq = '{{ $order->order_seq }}';

        fetch('{{ route("admin.order.update_status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                order_seq: orderSeq,
                step: step
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('상태가 변경되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('통신 오류가 발생했습니다.');
        });
    }
    </script>
@endsection
