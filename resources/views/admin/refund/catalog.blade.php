@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">환불 관리</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="form-inline">
                        <select name="status" class="form-control mr-2">
                            <option value="">전체 상태</option>
                            <option value="request" {{ $status == 'request' ? 'selected' : '' }}>환불요청</option>
                            <option value="ing" {{ $status == 'ing' ? 'selected' : '' }}>처리중</option>
                            <option value="complete" {{ $status == 'complete' ? 'selected' : '' }}>처리완료</option>
                        </select>
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="환불번호/주문번호/주문자" value="{{ $keyword }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>환불번호</th>
                                <th>주문번호</th>
                                <th>신청일</th>
                                <th>주문자/회원ID</th>
                                <th>환불유형</th>
                                <th>환불 예정 금액</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refunds as $item)
                            <tr>
                                <td>{{ $item->refund_code }}</td>
                                <td>
                                    <a href="{{ route('admin.order.view', $item->order_seq) }}">{{ $item->order_id }}</a>
                                </td>
                                <td>{{ substr($item->regist_date, 0, 10) }}</td>
                                <td>
                                    {{ $item->order_user_name }}
                                    @if($item->userid) <br><small class="text-muted">({{ $item->userid }})</small> @endif
                                </td>
                                <td>
                                    @if($item->refund_type == 'cancel_payment') 결제취소
                                    @elseif($item->refund_type == 'return') 반품환불
                                    @elseif($item->refund_type == 'shipping_price') 배송비환불
                                    @else {{ $item->refund_type }}
                                    @endif
                                </td>
                                <td class="text-right text-danger font-weight-bold">
                                    {{ number_format($item->refund_price) }}원
                                </td>
                                <td class="text-center">
                                    @if($item->status == 'request')
                                        <span class="badge badge-warning">신청</span>
                                    @elseif($item->status == 'ing')
                                        <span class="badge badge-info">처리중</span>
                                    @elseif($item->status == 'complete')
                                        <span class="badge badge-success">완료</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="alert('상세보기 준비중')">상세</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">환불 신청 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $refunds->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
