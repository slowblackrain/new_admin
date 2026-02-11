@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">반품 상세 정보 ({{ $return->return_code }})</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('seller.return.index') }}">반품 리스트</a></li>
                    <li class="breadcrumb-item active">반품 상세</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">주문 정보</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 140px;">주문번호</th>
                            <td>{{ $return->order_seq }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주문일시</th>
                            <td>{{ $return->order->regist_date ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주문자</th>
                            <td>{{ $return->order->order_user_name ?? '-' }} ({{ $return->order->order_cellphone ?? '-' }})</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
         <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">수거지 정보</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                         <tr>
                            <th scope="row" style="width: 140px;">수거자명</th>
                            <td>{{ $return->sender_name ?? $return->order->recipient_user_name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">연락처</th>
                            <td>{{ $return->sender_cellphone ?? $return->order->recipient_cellphone }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주소</th>
                            <td>
                                [{{ $return->sender_zipcode ?? $return->order->recipient_zipcode }}]<br>
                                {{ $return->sender_address ?? $return->order->recipient_address }}
                                {{ $return->sender_address_detail ?? $return->order->recipient_address_detail }}
                            </td>
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
                <h5 class="card-title mb-0">반품 상품 목록</h5>
            </div>
            <div class="card-body">
                 <div class="table-responsive">
                    <table class="table table-centered table-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th>이미지</th>
                                <th>상품명/옵션</th>
                                <th>반품수량</th>
                                <th>반품사유</th>
                                <th>처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                                <tr>
                                    <td>
                                        <!-- Image handling logic consistent with legacy/new system -->
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
                                    <td>{{ $item->reason_desc ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $return->status == 'complete' ? 'success' : 'secondary' }}">
                                            {{ $return->status_label }}
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
        <a href="{{ route('seller.return.index') }}" class="btn btn-secondary">목록으로</a>
    </div>
</div>
@endsection
