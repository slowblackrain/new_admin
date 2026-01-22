@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">연동주문관리</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Search Filter -->
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">검색 필터</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <form method="GET" action="{{ route('seller.order.catalog') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>기간</label>
                                    <div class="input-group">
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">~</span>
                                        </div>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>키워드 (주문번호/주문자/수령자)</label>
                                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="검색어 입력">
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 32px;">
                                <button type="submit" class="btn btn-primary btn-block">검색</button>
                            </div>
                            <div class="col-md-1" style="margin-top: 32px;">
                                <a href="{{ route('seller.order.catalog') }}" class="btn btn-default btn-block">초기화</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">주문 리스트 (총 {{ $orders->total() }}건)</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                        <tr>
                            <th>주문일시</th>
                            <th>주문번호</th>
                            <th>주문자</th>
                            <th>상품정보</th>
                            <th>결제금액</th>
                            <th>상태</th>
                            <th>관리</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->regist_date }}</td>
                                <td>
                                    <strong>{{ $order->order_seq }}</strong>
                                </td>
                                <td>
                                    {{ $order->order_user_name }}<br>
                                    <span class="text-muted text-sm">{{ $order->userid ?? '비회원' }}</span>
                                </td>
                                <td>
                                    @php
                                        // As a Reseller (Buyer), they see ALL items they bought.
                                        $firstItem = $order->items->first();
                                        $itemCount = $order->items->count();
                                        $firstOption = $firstItem ? $firstItem->options->first() : null;
                                    @endphp
                                    @if($firstItem)
                                        {{ $firstItem->goods_name }}
                                        @if($itemCount > 1)
                                            <span class="badge badge-info ml-1">+{{ $itemCount - 1 }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            @if($firstOption)
                                                {{ $firstOption->option1 }}{{ $firstOption->option2 ? ' / '.$firstOption->option2 : '' }}
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-danger">표시할 상품 없음</span>
                                    @endif
                                </td>
                                <td>{{ number_format($order->settleprice) }}원</td>
                                <td>
                                    <span class="badge badge-{{ $order->step >= 25 ? 'success' : 'warning' }}">
                                        Step {{ $order->step }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('seller.order.view', $order->order_seq) }}" class="btn btn-sm btn-info">상세보기</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">검색된 주문이 없습니다.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
