@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">출고 상세 정보 ({{ $export->export_code }})</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('seller.export.catalog') }}">출고 리스트</a></li>
                    <li class="breadcrumb-item active">출고 상세</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Order Info --}}
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
                            <td>{{ $export->order_seq }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주문일시</th>
                            <td>{{ $export->regist_date->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주문자명</th>
                            <td>{{ $export->order->order_user_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">연락처</th>
                            <td>{{ $export->order->order_cellphone ?? '-' }}</td>
                        </tr>
                         <tr>
                            <th scope="row">이메일</th>
                            <td>{{ $export->order->order_email ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recipient Info --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">배송지 정보</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 140px;">수령자명</th>
                            <td>{{ $export->order->recipient_user_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">연락처</th>
                            <td>{{ $export->order->recipient_cellphone ?? '-' }} / {{ $export->order->recipient_phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">주소</th>
                            <td>
                                [{{ $export->order->recipient_zipcode ?? '-' }}]<br>
                                {{ $export->order->recipient_address ?? '-' }} {{ $export->order->recipient_address_detail ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">배송메세지</th>
                            <td>{{ $export->order->memo ?? '-' }}</td>
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
                <h5 class="card-title mb-0">출고 상품 목록</h5>
            </div>
            <div class="card-body">
                 <div class="table-responsive">
                    <table class="table table-centered table-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th>이미지</th>
                                <th>상품명/옵션</th>
                                <th>수량</th>
                                <th>상태</th>
                                <th>송장번호</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($export->items as $item)
                                <tr>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ $item->image }}" alt="product-img" title="product-img" class="avatar-md">
                                        @else
                                             <div class="avatar-md bg-light rounded d-flex align-items-center justify-content-center">
                                                <i class="bx bx-image-alt font-size-24 text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <h5 class="font-size-14 text-truncate"><a href="javascript: void(0);" class="text-dark">{{ $item->goods_name }}</a></h5>
                                        <p class="text-muted mb-0">
                                            @if($item->option1) {{ $item->option1 }} @endif
                                            @if($item->option2) / {{ $item->option2 }} @endif
                                            @if($item->option3) / {{ $item->option3 }} @endif
                                        </p>
                                    </td>
                                    <td>{{ number_format($item->ea) }}</td>
                                    <td>
                                         <span class="badge badge-pill badge-{{ $export->status == '55' ? 'success' : 'secondary' }} font-size-12">
                                            {{ \App\Models\GoodsExport::getStatusName($export->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($export->delivery_number)
                                            {{ $export->delivery_number }}
                                        @else
                                            <span class="text-muted">미입력</span>
                                        @endif
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
@endsection
