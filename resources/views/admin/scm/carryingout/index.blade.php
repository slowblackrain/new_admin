@extends('admin.layouts.master')

@section('content')
<div class="page-header">
    <h3 class="page-title">반출 내역</h3>
    <div class="page-actions">
        <a href="{{ route('admin.scm_carryingout.create') }}" class="btn btn-danger">반출 등록 (폐기/반품)</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_carryingout.index') }}" method="GET" class="form-inline">
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
                <select name="sc_cro_status" class="form-control form-control-sm">
                    <option value="">전체 상태</option>
                    <option value="0" {{ request('sc_cro_status') === '0' ? 'selected' : '' }}>대기</option>
                    <option value="1" {{ request('sc_cro_status') == '1' ? 'selected' : '' }}>완료</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="반출코드, 거래처명">
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
                    <th>반출코드</th>
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
                    <td>{{ $item->cro_seq }}</td>
                    <td>{{ $item->cro_code }}</td>
                    <td>{{ $item->trader_name }}</td>
                    <td>{{ $item->wh_name }}</td>
                    <td>{{ substr($item->regist_date, 0, 10) }}</td>
                    <td>{{ $item->complete_date ? substr($item->complete_date, 0, 10) : '-' }}</td>
                    <td>
                        @if($item->cro_status == '1') <span class="text-danger font-weight-bold">완료</span>
                        @else <span class="text-secondary">대기</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.scm_carryingout.create', ['cro_seq' => $item->cro_seq]) }}" class="btn btn-sm btn-outline-primary">상세</a>
                        @if($item->cro_status == '1')
                        <form action="{{ route('admin.scm_carryingout.destroy') }}" method="POST" style="display:inline-block;" onsubmit="return confirm('반출 내역을 삭제하고 재고를 복구하시겠습니까?');">
                            @csrf
                            <input type="hidden" name="cro_seq" value="{{ $item->cro_seq }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">삭제(복구)</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">데이터가 없습니다.</td>
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
