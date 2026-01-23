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
            <!-- Step Tabs -->
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ !$currentStep ? 'active' : '' }}" href="{{ route('admin.order.catalog') }}">
                                전체 ({{ number_format($stepCounts['total']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '15' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '15']) }}">
                                주문접수 ({{ number_format($stepCounts['15']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '25' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '25']) }}">
                                결제확인 ({{ number_format($stepCounts['25']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '35_45' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '35_45']) }}">
                                상품준비 ({{ number_format($stepCounts['35_45']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '50_55' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '50_55']) }}">
                                출고 ({{ number_format($stepCounts['50_55']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '60_65' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '60_65']) }}">
                                배송중 ({{ number_format($stepCounts['60_65']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '70' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '70']) }}">
                                배송완료 ({{ number_format($stepCounts['70']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '75' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '75']) }}">
                                구매확정 ({{ number_format($stepCounts['75']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '85' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '85']) }}">
                                거래완료 ({{ number_format($stepCounts['85']) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentStep == '95' ? 'active' : '' }}" href="{{ route('admin.order.catalog', ['step' => '95']) }}">
                                주문취소 ({{ number_format($stepCounts['95']) }})
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">주문 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.order.catalog') }}" method="GET" class="form-inline">
                        <!-- Maintain current step in search -->
                        @if($currentStep)
                            <input type="hidden" name="step" value="{{ $currentStep }}">
                        @endif
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="주문번호/주문자명" value="{{ request('keyword') }}">
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
                                        <span class="badge" style="background-color: {{ \App\Models\Order::getStepColor($order->step) }}; color: #fff;">
                                            {{ \App\Models\Order::getStepName($order->step) }}
                                        </span>
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
