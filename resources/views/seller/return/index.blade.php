@extends('seller.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">반품 관리</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">주문 관리</a></li>
                    <li class="breadcrumb-item active">반품 리스트</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('seller.return.index') }}" method="GET" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">접수일</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        <span class="mx-2">~</span>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    
                    <div class="form-group mr-2">
                        <select name="status" class="form-control">
                            <option value="">전체 상태</option>
                            <option value="request" {{ request('status') == 'request' ? 'selected' : '' }}>반품신청</option>
                            <option value="ing" {{ request('status') == 'ing' ? 'selected' : '' }}>반품처리중</option>
                            <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>반품완료</option>
                        </select>
                    </div>

                    <div class="form-group mr-2">
                        <input type="text" name="keyword" class="form-control" placeholder="반품코드, 주문번호" value="{{ request('keyword') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="{{ route('seller.return.index') }}" class="btn btn-secondary ml-2">초기화</a>
                    <button type="button" class="btn btn-success ml-auto" onclick="alert('엑셀 다운로드 기능은 구현 예정입니다.');"><i class="fas fa-file-excel mr-1"></i>엑셀 리스트 다운로드</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>반품코드</th>
                                <th>주문번호</th>
                                <th>접수일시</th>
                                <th>주문자/수령자</th>
                                <th>상품정보</th>
                                <th>반품수량</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                                <tr>
                                    <td>
                                        <a href="{{ route('seller.return.show', $return->return_seq) }}" class="text-body font-weight-bold">
                                            {{ $return->return_code }}
                                        </a>
                                    </td>
                                    <td>{{ $return->order_seq }}</td>
                                    <td>{{ \Carbon\Carbon::parse($return->regist_date)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        {{ $return->order->order_user_name ?? '-' }}<br>
                                        <small class="text-muted">{{ $return->order->recipient_user_name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($return->items->count() > 0)
                                            {{ $return->items->first()->orderItem->goods_name ?? '-' }}
                                            @if($return->items->count() > 1)
                                                외 {{ $return->items->count() - 1 }}건
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ number_format($return->items->sum('ea')) }}</td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $return->status == 'complete' ? 'success' : ($return->status == 'request' ? 'warning' : 'info') }} font-size-12">
                                            {{ $return->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.return.show', $return->return_seq) }}" class="btn btn-primary btn-sm btn-rounded">
                                            상세보기
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">검색된 반품 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <div class="float-right">
                            {{ $returns->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
