@extends('admin.layouts.admin')

@section('content')
<div class="page-header">
    <h3 class="page-title">거래처별 매입 분석</h3>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_analysis.trader') }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <input type="date" name="sc_sdate" value="{{ $startDate }}" class="form-control">
                ~
                <input type="date" name="sc_edate" value="{{ $endDate }}" class="form-control">
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
                    <th>순위</th>
                    <th>거래처명</th>
                    <th>입고 건수</th>
                    <th>공급가액</th>
                    <th>세액</th>
                    <th>합계금액</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list as $idx => $item)
                <tr>
                    <td>{{ ($list->currentPage() - 1) * $list->perPage() + $idx + 1 }}</td>
                    <td class="text-left font-weight-bold pl-3">{{ $item->trader_name }}</td>
                    <td>{{ number_format($item->cnt) }}</td>
                    <td>{{ number_format($item->total_amt) }}</td>
                    <td>{{ number_format($item->total_tax) }}</td>
                    <td class="text-primary font-weight-bold">{{ number_format($item->total_amt + $item->total_tax) }}</td>
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
