@extends('seller.layouts.app')

@section('title', '정산 내역')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">정산 내역</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">정산 내역</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">정산 목록</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('seller.account.index') }}" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <label class="mr-2">기간</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    <span class="mx-2">~</span>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary">검색</button>
            </form>

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>정산일자</th>
                        <th>정산금액</th>
                        <th>수수료</th>
                        <th>지급상태</th>
                        <th>비고</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                        <tr>
                            <td>{{ $acc->account_seq ?? '-' }}</td>
                            <td>{{ $acc->regist_date }}</td>
                            <td>{{ number_format($acc->account_price ?? 0) }}</td>
                            <td>{{ number_format($acc->commission_price ?? 0) }}</td>
                            <td>{{ $acc->status ?? '대기' }}</td>
                            <td>{{ $acc->memo ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">정산 내역이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $accounts->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
