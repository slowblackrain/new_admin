@extends('admin.layouts.admin')

@section('content')
<div class="page-header">
    <h3 class="page-title">거래처 정산 원장: {{ $trader->trader_name ?? 'Unknown' }} ({{ $trader->trader_id ?? '' }})</h3>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_trader_account.show', $traderSeq) }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <input type="date" name="sc_sdate" value="{{ $filters['sc_sdate'] }}" class="form-control">
                ~
                <input type="date" name="sc_edate" value="{{ $filters['sc_edate'] }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-secondary">검색</button>
            <a href="{{ route('admin.scm_trader_account.index') }}" class="btn btn-light ml-2">목록으로</a>
        </form>
    </div>
</div>

<div class="row">
    <!-- Ledger Table -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white font-weight-bold">
                정산 내역
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-hover text-center table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>일자</th>
                            <th>구분</th>
                            <th>내용</th>
                            <th>이월잔액</th>
                            <th>금액</th>
                            <th>잔액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detailList as $item)
                        <tr>
                            <td>{{ substr($item->act_date, 0, 10) }}</td>
                            <td>
                                @if($item->act_type == 'pay') <span class="badge badge-primary">지급</span>
                                @elseif($item->act_type == 'carryingout') <span class="badge badge-warning">반출</span>
                                @elseif($item->act_type == 'def') <span class="badge badge-secondary">이월</span>
                                @elseif($item->act_type == 'in') <span class="badge badge-info">입고</span>
                                @else <span class="badge badge-light">{{ $item->act_type }}</span>
                                @endif
                            </td>
                            <td class="text-left pl-2">
                                {{ $item->act_memo }}
                                @if($item->act_code) <div class="text-muted small">Code: {{ $item->act_code }}</div> @endif
                            </td>
                            <td class="text-right pr-2 text-muted small">{{ number_format($item->act_carriedover) }}</td>
                            <td class="text-right pr-2 font-weight-bold">
                                {{ number_format($item->act_price) }}
                            </td>
                            <td class="text-right pr-2 {{ $item->act_balance > 0 ? 'text-danger' : 'text-primary' }}">
                                {{ number_format($item->act_balance) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">내역이 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                 {{ $detailList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white font-weight-bold">
                정산 등록
            </div>
            <div class="card-body">
                <form action="{{ route('admin.scm_trader_account.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="trader_seq" value="{{ $traderSeq }}">
                    
                    <div class="form-group">
                        <label>일자</label>
                        <input type="date" name="act_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>구분</label>
                        <select name="act_type" class="form-control">
                            <option value="pay">지급 (현금/이체)</option>
                            <option value="def">이월 (기초잔액 조정)</option>
                            <option value="cal">기타 조정</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>금액</label>
                        <input type="number" name="act_price" class="form-control" placeholder="0" required>
                        <small class="form-text text-muted">지급 시 잔액감소, 이월 시 잔액증가</small>
                    </div>

                    <div class="form-group">
                        <label>메모</label>
                        <textarea name="act_memo" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">저장</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
