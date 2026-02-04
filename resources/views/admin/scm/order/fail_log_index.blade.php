@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">자동발주 실패 내역 (슈퍼관리자)</h3>
                <div class="card-tools">
                    <form action="{{ route('admin.scm_order.fail_log') }}" method="GET" class="form-inline">
                        <select name="status" class="form-control mr-2">
                            <option value="">전체 상태</option>
                            <option value="N" {{ request('status') == 'N' ? 'selected' : '' }}>미확인</option>
                            <option value="Y" {{ request('status') == 'Y' ? 'selected' : '' }}>확인완료</option>
                        </select>
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="입점사/상품명" value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>발주번호</th>
                            <th>입점사 (아이디)</th>
                            <th>상품명</th>
                            <th>실패사유</th>
                            <th>상태</th>
                            <th>발생일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->seq }}</td>
                            <td>{{ $log->sorder_seq }}</td>
                            <td>{{ $log->user_name }} ({{ $log->userid }})</td>
                            <td>{{ $log->goods_name }}</td>
                            <td class="text-danger">{{ $log->fail_reason }}</td>
                            <td>
                                @if($log->is_checked == 'Y')
                                    <span class="badge badge-success">확인완료</span>
                                @else
                                    <span class="badge badge-warning">미확인</span>
                                @endif
                            </td>
                            <td>{{ $log->regist_date }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">실패 내역이 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
