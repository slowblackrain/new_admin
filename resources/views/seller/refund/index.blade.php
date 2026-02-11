@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">환불 관리</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">주문 관리</a></li>
                    <li class="breadcrumb-item active">환불 리스트</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('seller.refund.index') }}" method="GET" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">접수일</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        <span class="mx-2">~</span>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    
                    <div class="form-group mr-2">
                        <select name="status" class="form-control">
                            <option value="">전체 상태</option>
                            <option value="request" {{ request('status') == 'request' ? 'selected' : '' }}>환불신청</option>
                            <option value="ing" {{ request('status') == 'ing' ? 'selected' : '' }}>환불처리중</option>
                            <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>환불완료</option>
                        </select>
                    </div>

                    <div class="form-group mr-2">
                        <input type="text" name="keyword" class="form-control" placeholder="환불코드, 주문번호" value="{{ request('keyword') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="{{ route('seller.refund.index') }}" class="btn btn-secondary ml-2">초기화</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>환불코드</th>
                                <th>주문번호</th>
                                <th>접수일시</th>
                                <th>주문자</th>
                                <th>상품정보</th>
                                <th>환불금액</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refunds as $refund)
                                <tr>
                                    <td>
                                        <a href="{{ route('seller.refund.show', $refund->refund_seq) }}" class="text-body font-weight-bold">
                                            {{ $refund->refund_code }}
                                        </a>
                                    </td>
                                    <td>{{ $refund->order_seq }}</td>
                                    <td>{{ \Carbon\Carbon::parse($refund->regist_date)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        {{ $refund->order->order_user_name ?? '-' }}
                                    </td>
                                    <td>
                                        @if($refund->items->count() > 0)
                                            {{ $refund->items->first()->orderItem->goods_name ?? '-' }}
                                            @if($refund->items->count() > 1)
                                                외 {{ $refund->items->count() - 1 }}건
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ number_format($refund->refund_price) }}원</td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $refund->status == 'complete' ? 'success' : ($refund->status == 'request' ? 'warning' : 'info') }} font-size-12">
                                            {{ $refund->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.refund.show', $refund->refund_seq) }}" class="btn btn-primary btn-sm btn-rounded">
                                            상세보기
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">검색된 환불 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <div class="float-right">
                            {{ $refunds->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
