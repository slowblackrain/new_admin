@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">출고 관리 (Order Fulfillment)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">주문 관리</a></li>
                    <li class="breadcrumb-item active">출고 리스트</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('seller.export.catalog') }}" method="GET" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">주문일</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        <span class="mx-2">~</span>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    
                    <div class="form-group mr-2">
                        <select name="status" class="form-control">
                            <option value="">전체 상태</option>
                            <option value="45" {{ request('status') == '45' ? 'selected' : '' }}>출고준비</option>
                            <option value="55" {{ request('status') == '55' ? 'selected' : '' }}>출고완료</option>
                            <option value="75" {{ request('status') == '75' ? 'selected' : '' }}>반품접수</option>
                        </select>
                    </div>

                    <div class="form-group mr-2">
                        <input type="text" name="keyword" class="form-control" placeholder="출고번호, 주문번호, 주문자명" value="{{ request('keyword') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="{{ route('seller.export.catalog') }}" class="btn btn-secondary ml-2">초기화</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>출고번호</th>
                                <th>주문번호</th>
                                <th>주문일시</th>
                                <th>주문자/수령자</th>
                                <th>상품정보</th>
                                <th>결제금액</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exports as $export)
                                <tr>
                                    <td>
                                        <a href="{{ route('seller.export.view', $export->export_seq) }}" class="text-body font-weight-bold">
                                            {{ $export->export_code }}
                                        </a>
                                    </td>
                                    <td>{{ $export->order_seq }}</td>
                                    <td>
                                        {{ $export->regist_date->format('Y-m-d H:i') }}<br>
                                        <small class="text-muted">{{ $export->shipping_date ? $export->shipping_date->format('Y-m-d H:i') : '-' }}</small>
                                    </td>
                                    <td>
                                        {{ $export->order->order_user_name ?? '-' }}<br>
                                        <small class="text-muted">{{ $export->order->recipient_user_name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($export->items->count() > 0)
                                            {{ $export->items->first()->goods_name }}
                                            @if($export->items->count() > 1)
                                                외 {{ $export->items->count() - 1 }}건
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ number_format($export->items->sum(function($item){ return ($item->price + $item->option_price) * $item->ea; })) }}원
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $export->status == '55' ? 'success' : ($export->status == '45' ? 'warning' : 'secondary') }} font-size-12">
                                            {{ \App\Models\GoodsExport::getStatusName($export->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.export.view', $export->export_seq) }}" class="btn btn-primary btn-sm btn-rounded">
                                            상세보기
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">검색된 출고 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-sm-12 col-md-5">
                        <div class="dataTables_info" role="status" aria-live="polite">
                            Showing {{ $exports->firstItem() }} to {{ $exports->lastItem() }} of {{ $exports->total() }} entries
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers float-right">
                            {{ $exports->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
