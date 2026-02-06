@extends('admin.layouts.master')

@section('content')
<div class="page-header">
    <h3 class="page-title">입고 내역</h3>
    <div class="page-actions">
        <!-- Manual Warehousing (Exception) -->
        <a href="{{ route('admin.scm_warehousing.create', ['whs_type' => 'E']) }}" class="btn btn-primary">비정규 입고 등록</a>
        <!-- Auto/Standard Warehousing usually starts from Order List, but can support direct if needed -->
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_warehousing.index') }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <select name="sc_date_fld" class="form-control form-control-sm">
                    <option value="regist_date" {{ request('sc_date_fld') == 'regist_date' ? 'selected' : '' }}>등록일</option>
                    <option value="complete_date" {{ request('sc_date_fld') == 'complete_date' ? 'selected' : '' }}>완료일</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <input type="date" name="sc_sdate" value="{{ $filters['sc_sdate'] }}" class="form-control form-control-sm">
                ~
                <input type="date" name="sc_edate" value="{{ $filters['sc_edate'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mr-2">
                <select name="sc_whs_status" class="form-control form-control-sm">
                    <option value="">전체 상태</option>
                    <option value="0" {{ request('sc_whs_status') === '0' ? 'selected' : '' }}>대기</option>
                    <option value="1" {{ request('sc_whs_status') == '1' ? 'selected' : '' }}>완료</option>
                </select>
            </div>
             <div class="form-group mr-2">
                <select name="sc_whs_type" class="form-control form-control-sm">
                    <option value="">전체 유형</option>
                    <option value="S" {{ request('sc_whs_type') == 'S' ? 'selected' : '' }}>정규</option>
                    <option value="E" {{ request('sc_whs_type') == 'E' ? 'selected' : '' }}>비정규</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="입고코드, 거래처명">
            </div>
            <button type="submit" class="btn btn-sm btn-secondary">검색</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover text-center">
            <thead class="thead-light">
                <tr>
                    <th>번호</th>
                    <th>입고코드</th>
                    <th>유형</th>
                    <th>거래처</th>
                    <th>창고</th>
                    <th>등록일</th>
                    <th>완료일</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list as $item)
                <tr>
                    <td>{{ $item->whs_seq }}</td>
                    <td>{{ $item->whs_code }}</td>
                    <td>
                        @if($item->whs_type == 'S') <span class="badge badge-info">정규</span>
                        @else <span class="badge badge-warning">비정규</span>
                        @endif
                    </td>
                    <td>{{ $item->trader_name }}</td>
                    <td>{{ $item->wh_name }}</td>
                    <td>{{ substr($item->regist_date, 0, 10) }}</td>
                    <td>{{ $item->complete_date ? substr($item->complete_date, 0, 10) : '-' }}</td>
                    <td>
                        @if($item->whs_status == '1') <span class="text-primary font-weight-bold">완료</span>
                        @else <span class="text-secondary">대기</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.scm_warehousing.create', ['whs_seq' => $item->whs_seq]) }}" class="btn btn-sm btn-outline-primary">상세</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">데이터가 없습니다.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $list->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
