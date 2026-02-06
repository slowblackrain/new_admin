@extends('admin.layouts.admin')

@section('content')
<div class="page-header">
    <h3 class="page-title">정산 관리 (거래처별 잔액)</h3>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_trader_account.index') }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <input type="date" name="sc_sdate" value="{{ $filters['sc_sdate'] }}" class="form-control">
                ~
                <input type="date" name="sc_edate" value="{{ $filters['sc_edate'] }}" class="form-control">
            </div>
            <div class="form-group mr-2">
                <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="form-control" placeholder="거래처명 검색">
            </div>
            <button type="submit" class="btn btn-secondary">검색</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover text-center">
            <thead class="thead-light">
                <tr>
                    <th>거래처명</th>
                    <th>이월금액 (기간 전)</th>
                    <th>기간 입고금액 (지급예정)</th>
                    <th>기간 지급금액</th>
                    <th>현재 잔액</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list as $item)
                <tr>
                    <td class="text-left pl-3 font-weight-bold">{{ $item->trader_name }} ({{ $item->trader_id }})</td>
                    <td class="text-right pr-3">{{ number_format($item->carriedover) }}</td>
                    <td class="text-right pr-3">{{ number_format($item->act_in_price) }}</td>
                    <td class="text-right pr-3">{{ number_format($item->act_out_price) }}</td>
                    <td class="text-right pr-3 font-weight-bold {{ $item->balance > 0 ? 'text-danger' : 'text-primary' }}">
                        {{ number_format($item->balance) }}
                    </td>
                    <td>
                        <a href="{{ route('admin.scm_trader_account.show', $item->trader_seq) }}" class="btn btn-sm btn-info">원장조회</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">데이터가 없습니다.</td>
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
