@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">재고 수불부 (Stock Ledger)</h2>
        
        <!-- Search Filter -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title">검색 조건</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.scm_manage.ledger') }}" method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="sc_sdate" class="mr-2">기간</label>
                        <input type="date" class="form-control" id="sc_sdate" name="sc_sdate" value="{{ $filters['start_date'] }}">
                        <span class="mx-2">~</span>
                        <input type="date" class="form-control" id="sc_edate" name="sc_edate" value="{{ $filters['end_date'] }}">
                    </div>

                    <div class="form-group mx-2">
                        <label for="wh_seq" class="mr-2">창고</label>
                        <select class="form-control" id="wh_seq" name="wh_seq">
                            <option value="">전체 창고</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->wh_seq }}" {{ $filters['wh_seq'] == $wh->wh_seq ? 'selected' : '' }}>
                                    {{ $wh->wh_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mx-2">
                        <label for="keyword" class="mr-2">검색어</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" value="{{ $filters['keyword'] }}" placeholder="상품명/코드">
                    </div>

                    <button type="submit" class="btn btn-primary ml-2">검색</button>
                    <a href="{{ route('admin.scm_manage.ledger') }}" class="btn btn-secondary ml-2">초기화</a>
                </form>
            </div>
                </form>
            </div>
            <div class="card-footer clearfix text-right">
                <button type="button" class="btn btn-secondary btn-sm" onclick="openLedgerPrint();">
                    <i class="fas fa-print"></i> 인쇄
                </button>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;">일자</th>
                                <th rowspan="2" style="vertical-align: middle;">상품명 / 코드 [카테고리]</th>
                                <th colspan="3">기초 재고 (Previous)</th>
                                <th colspan="3">입고 (In)</th>
                                <th colspan="3">출고 (Out)</th>
                                <th colspan="3">기말 재고 (Current)</th>
                            </tr>
                            <tr>
                                <!-- Pre -->
                                <th>수량</th>
                                <th>단가</th>
                                <th>금액</th>
                                <!-- In -->
                                <th>수량</th>
                                <th>단가</th> <!-- Actually Total/Qty -->
                                <th>금액</th>
                                <!-- Out -->
                                <th>수량</th>
                                <th>단가</th> <!-- Avg Cost -->
                                <th>금액</th>
                                <!-- Cur -->
                                <th>수량</th>
                                <th>단가</th>
                                <th>금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->ldg_date }}</td>
                                    <td class="text-left" onclick="sel_ledger('{{ $log->goods_seq }}', 'option', '{{ $log->option_seq ?? 0 }}', '{{ $log->calc_out_unit_price }}')" style="cursor:pointer; color:#007bff;">
                                        <div class="font-weight-bold">{{ $log->goods_name }}</div>
                                        <div class="small text-muted">{{ $log->goods_code }}</div>
                                        @if($log->scm_category)
                                            <span class="badge badge-light">{{ $log->scm_category }}</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Pre -->
                                    <td>{{ number_format($log->pre_ea) }}</td>
                                    <td>{{ number_format($log->pre_supply_price) }}</td>
                                    <td>{{ number_format($log->calc_pre_price) }}</td>

                                    <!-- In -->
                                    <td>{{ number_format($log->in_ea) }}</td>
                                    <td>
                                        @if($log->in_ea > 0)
                                            {{ number_format($log->calc_in_price / $log->in_ea) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->calc_in_price) }}</td>

                                    <!-- Out (Calculated Avg Cost) -->
                                    <td>{{ number_format($log->out_ea) }}</td>
                                    <td class="text-primary font-weight-bold">{{ number_format($log->calc_out_unit_price) }}</td>
                                    <td>{{ number_format($log->calc_out_price) }}</td>

                                    <!-- Cur -->
                                    <td>{{ number_format($log->cur_ea) }}</td>
                                    <td class="text-primary font-weight-bold">{{ number_format($log->calc_cur_unit_price) }}</td>
                                    <td>{{ number_format($log->calc_cur_price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center py-4">조회된 데이터가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $logs->appends($filters)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@section('custom_js')
<script>
    function sel_ledger(goods_seq, option_type, option_seq, price) {
        var sdate = document.getElementById('sc_sdate').value;
        var edate = document.getElementById('sc_edate').value;
        var wh_seq = document.getElementById('wh_seq').value;
        
        var url = "{{ route('admin.scm_manage.ledger_detail') }}";
        url += "?goods_seq=" + goods_seq;
        url += "&option_type=" + option_type;
        url += "&option_seq=" + option_seq; // Pass option_seq for specific SKU history
        url += "&out_supply_price=" + price;
        url += "&sc_sdate=" + sdate;
        url += "&sc_edate=" + edate;
        url += "&wh_seq=" + wh_seq;

        // Open popup
        window.open(url, 'ledger_detail', 'width=1000,height=800,accumulate=yes,scrollbars=yes,resizable=yes');
    }

    function openLedgerPrint() {
        var sdate = document.getElementById('sc_sdate').value;
        var edate = document.getElementById('sc_edate').value;
        var wh_seq = document.getElementById('wh_seq').value;
        var keyword = document.getElementById('keyword').value;

        var url = "{{ route('admin.scm_manage.ledger_print') }}";
        url += "?sc_sdate=" + sdate;
        url += "&sc_edate=" + edate;
        url += "&wh_seq=" + wh_seq;
        url += "&keyword=" + encodeURIComponent(keyword);

        window.open(url, 'ledger_print', 'width=1000,height=800,accumulate=yes,scrollbars=yes,resizable=yes');
    }
</script>
@endsection
@endsection
