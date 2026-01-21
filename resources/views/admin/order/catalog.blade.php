@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">전체 주문리스트</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">주문 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.order.catalog') }}" method="GET" class="form-inline">
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="주문번호/주문자명" value="{{ request('keyword') }}">
                        <select name="step" class="form-control mr-2">
                            <option value="">전체 상태</option>
                            <option value="15" {{ request('step') == 15 ? 'selected' : '' }}>주문접수 (15)</option>
                            <option value="25" {{ request('step') == 25 ? 'selected' : '' }}>배송준비 (25)</option>
                            <!-- Add more steps as needed -->
                        </select>
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped">
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
                                    <td>{{ $order->order_seq }}</td>
                                    <td>{{ $order->order_user_name }}</td>
                                    <td>
                                        @if($order->items->count() > 0)
                                            {{ $order->items->first()->goods_name }}
                                            @if($order->items->count() > 1)
                                                외 {{ $order->items->count() - 1 }}건
                                            @endif
                                        @else
                                            상품 정보 없음
                                        @endif
                                    </td>
                                    <td>{{ number_format($order->settleprice) }}원</td>
                                    <td>
                                        <span class="badge badge-info">{{ $order->step }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.order.view', $order->order_seq) }}" class="btn btn-sm btn-secondary">상세</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">주문 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $orders->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
