@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SCM 발주 목록 (SCM Order List)</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.scm_order.index') }}" class="mb-4">
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <input type="date" class="form-control mb-2" name="sc_sdate" value="{{ $filters['sc_sdate'] }}">
                        </div>
                        <div class="col-auto">
                            <span class="mb-2">~</span>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control mb-2" name="sc_edate" value="{{ $filters['sc_edate'] }}">
                        </div>
                        <div class="col-auto">
                            <select class="form-control mb-2" name="sc_sorder_status">
                                <option value="">Status: All</option>
                                <option value="0" {{ (isset($filters['sc_sorder_status']) && $filters['sc_sorder_status'] == '0') ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ (isset($filters['sc_sorder_status']) && $filters['sc_sorder_status'] == '1') ? 'selected' : '' }}>Ordered</option>
                                <option value="2" {{ (isset($filters['sc_sorder_status']) && $filters['sc_sorder_status'] == '2') ? 'selected' : '' }}>Warehoused</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control mb-2" name="keyword" placeholder="Code / Trader" value="{{ $filters['keyword'] ?? '' }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-2">Search</button>
                            <a href="{{ route('admin.scm_order.index') }}" class="btn btn-secondary mb-2">Reset</a>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-hover text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>발주코드 (Code)</th>
                            <th>주거래처 (Trader)</th>
                            <th>총수량 (Total EA)</th>
                            <th>총금액 (Total Price)</th>
                            <th>상태 (Status)</th>
                            <th>등록일 (Date)</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->sorder_seq }}</td>
                            <td><a href="{{ route('admin.scm_order.create', ['sorder_seq' => $order->sorder_seq]) }}">{{ $order->sorder_code }}</a></td>
                            <td>{{ $order->trader_name }}</td>
                            <td class="text-right">{{ number_format($order->total_ea) }}</td>
                            <td class="text-right">{{ number_format($order->krw_total_price) }}</td>
                            <td>
                                @if($order->sorder_status == 0) <span class="badge badge-warning">임시저장 (Draft)</span>
                                @elseif($order->sorder_status == 1) <span class="badge badge-primary">발주완료 (Ordered)</span>
                                @elseif($order->sorder_status == 2) <span class="badge badge-success">입고완료 (Warehoused)</span>
                                @else <span class="badge badge-secondary">{{ $order->sorder_status }}</span>
                                @endif
                            </td>
                            <td>{{ substr($order->regist_date, 0, 10) }}</td>
                            <td>
                                <a href="{{ route('admin.scm_order.create', ['sorder_seq' => $order->sorder_seq]) }}" class="btn btn-sm btn-info">상세</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">데이터가 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $orders->appends($filters)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
