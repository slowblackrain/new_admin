@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">환불 상세 정보 ({{ $refund->refund_code }})</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('seller.refund.index') }}">환불 리스트</a></li>
                    <li class="breadcrumb-item active">환불 상세</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">주문 및 환불금액 정보</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 140px;">주문번호</th>
                            <td>{{ $refund->order_seq }}</td>
                            <th scope="row" style="width: 140px;">총 환불금액</th>
                            <td class="text-danger font-weight-bold">{{ number_format($refund->refund_price) }}원</td>
                        </tr>
                        <tr>
                            <th scope="row">주문자</th>
                            <td>{{ $refund->order->order_user_name ?? '-' }} ({{ $refund->order->order_cellphone ?? '-' }})</td>
                            <th scope="row">환불수단</th>
                            <td>{{ $refund->refund_type ?? '-' }}</td>
                        </tr>
                         <tr>
                            <th scope="row">환불사유</th>
                            <td colspan="3">{{ $refund->refund_reason ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">환불 상품 목록</h5>
            </div>
            <div class="card-body">
                 <div class="table-responsive">
                    <table class="table table-centered table-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th>이미지</th>
                                <th>상품명/옵션</th>
                                <th>수량</th>
                                <th>환불 접수 금액</th>
                                <th>처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($refund->items as $item)
                                <tr>
                                    <td>
                                        @php $imgSrc = $item->orderItem->image ?? '/images/no_image.png'; @endphp
                                        <img src="{{ $imgSrc }}" alt="product-img" class="avatar-md bg-light rounded">
                                    </td>
                                    <td>
                                        <h5 class="font-size-14 text-truncate">
                                            {{ $item->orderItem->goods_name ?? '상품정보 없음' }}
                                        </h5>
                                        <p class="text-muted mb-0">
                                            {{ $item->orderItem->option1 ?? '' }}
                                        </p>
                                    </td>
                                    <td>{{ number_format($item->ea) }}</td>
                                    <td>{{ number_format(($item->price ?? 0) * $item->ea) }}원</td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $refund->status == 'complete' ? 'success' : 'secondary' }}">
                                            {{ $refund->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 text-center mb-4">
        <a href="{{ route('seller.refund.index') }}" class="btn btn-secondary">목록으로</a>
    </div>
</div>
@endsection
