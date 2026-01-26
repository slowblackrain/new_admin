@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">반품 관리</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="form-inline">
                        <select name="status" class="form-control mr-2">
                            <option value="">전체 상태</option>
                            <option value="request" {{ $status == 'request' ? 'selected' : '' }}>반품신청</option>
                            <option value="ing" {{ $status == 'ing' ? 'selected' : '' }}>처리중</option>
                            <option value="complete" {{ $status == 'complete' ? 'selected' : '' }}>처리완료</option>
                        </select>
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="반품번호/주문번호/주문자" value="{{ $keyword }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>반품번호</th>
                                <th>주문번호</th>
                                <th>신청일</th>
                                <th>주문자/회원ID</th>
                                <th>반품사유</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $item)
                            <tr>
                                <td>{{ $item->return_code }}</td>
                                <td>
                                    <a href="{{ route('admin.order.view', $item->order_seq) }}">{{ $item->order_id }}</a>
                                </td>
                                <td>{{ substr($item->regist_date, 0, 10) }}</td>
                                <td>
                                    {{ $item->order_user_name }}
                                    @if($item->userid) <br><small class="text-muted">({{ $item->userid }})</small> @endif
                                </td>
                                <td>{{ Str::limit($item->return_reason, 30) }}</td>
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
                                <td colspan="7" class="text-center py-4">반품 신청 내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $returns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
