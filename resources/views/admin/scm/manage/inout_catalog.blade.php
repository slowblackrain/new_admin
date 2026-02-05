@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">기간별 수불부 집계 (In/Out History)</h2>

        <div class="card shadow mb-4">
            <div class="card-header">
                <form action="{{ route('admin.scm_manage.inout_catalog') }}" method="GET" class="form-inline">
                    <label class="sr-only" for="sc_sdate">시작일</label>
                    <input type="date" class="form-control mb-2 mr-sm-2" id="sc_sdate" name="sc_sdate" value="{{ $filters['start_date'] }}">
                    <span>~</span>
                    <label class="sr-only" for="sc_edate">종료일</label>
                    <input type="date" class="form-control mb-2 mr-sm-2 ml-sm-2" id="sc_edate" name="sc_edate" value="{{ $filters['end_date'] }}">

                    <label class="sr-only" for="wh_seq">창고</label>
                    <select class="form-control mb-2 mr-sm-2 ml-2" id="wh_seq" name="wh_seq">
                        <option value="">전체 창고</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->wh_seq }}" {{ ($filters['wh_seq'] == $wh->wh_seq) ? 'selected' : '' }}>
                                {{ $wh->wh_name }}
                            </option>
                        @endforeach
                    </select>

                    <label class="sr-only" for="keyword">키워드</label>
                    <input type="text" class="form-control mb-2 mr-sm-2 ml-2" id="keyword" name="keyword" value="{{ $filters['keyword'] }}" placeholder="상품명/코드">

                    <button type="submit" class="btn btn-primary mb-2 ml-2">검색</button>
                    <a href="{{ route('admin.scm_manage.inout_catalog') }}" class="btn btn-secondary mb-2 ml-2">초기화</a>
                    <button type="button" class="btn btn-success mb-2 ml-2" onclick="downloadExcel()">
                        <i class="fas fa-file-excel"></i> 엑셀 다운로드
                    </button>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" class="align-middle">상품정보</th>
                                <th colspan="2">기초재고 (Pre)</th>
                                <th colspan="2">입고 (In)</th>
                                <th colspan="2">출고 (Out)</th>
                                <th colspan="2">기말재고 (Cur)</th>
                            </tr>
                            <tr>
                                <th>수량</th>
                                <th>금액</th>
                                <th>수량</th>
                                <th>금액</th>
                                <th>수량</th>
                                <th>금액</th>
                                <th>수량</th>
                                <th>금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $item)
                                <tr>
                                    <td class="text-left" onclick="sel_ledger('{{ $item->goods_seq }}', '', '', '')" style="cursor:pointer; color:#007bff;">
                                        <strong>{{ $item->goods_name }}</strong><br>
                                        <span class="text-muted small">{{ $item->goods_code }}</span>
                                    </td>
                                    
                                    <!-- Pre -->
                                    <td class="text-right">{{ number_format($item->pre_ea) }}</td>
                                    <td class="text-right">{{ number_format($item->pre_amt) }}</td>
                                    
                                    <!-- In -->
                                    <td class="text-right">{{ number_format($item->in_ea) }}</td>
                                    <td class="text-right">{{ number_format($item->in_amt) }}</td>
                                    
                                    <!-- Out -->
                                    <td class="text-right">{{ number_format($item->out_ea) }}</td>
                                    <td class="text-right">{{ number_format($item->out_amt) }}</td>
                                    
                                    <!-- Cur -->
                                    <td class="text-right font-weight-bold">{{ number_format($item->cur_ea) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->cur_amt) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-4">데이터가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($history->count() > 0)
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td>합계</td>
                                <td class="text-right">{{ number_format($history->sum('pre_ea')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('pre_amt')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('in_ea')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('in_amt')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('out_ea')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('out_amt')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('cur_ea')) }}</td>
                                <td class="text-right">{{ number_format($history->sum('cur_amt')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    function sel_ledger(goods_seq, option_type, option_seq, price) {
        var sdate = document.getElementById('sc_sdate').value;
        var edate = document.getElementById('sc_edate').value;
        var wh_seq = document.getElementById('wh_seq').value;
        
        var url = "{{ route('admin.scm_manage.ledger_detail') }}";
        url += "?goods_seq=" + goods_seq;
        url += "&option_type=" + option_type;
        url += "&option_seq=" + option_seq;
        url += "&out_supply_price=" + price;
        url += "&sc_sdate=" + sdate;
        url += "&sc_edate=" + edate;
        url += "&wh_seq=" + wh_seq;

        // Open popup
        window.open(url, 'ledger_detail', 'width=1000,height=800,accumulate=yes,scrollbars=yes,resizable=yes');
    }

    function downloadExcel() {
        var sdate = document.getElementById('sc_sdate').value;
        var edate = document.getElementById('sc_edate').value;
        var wh_seq = document.getElementById('wh_seq').value;
        var keyword = document.getElementById('keyword').value;

        var url = "{{ route('admin.scm_manage.inout_catalog.excel') }}";
        url += "?sc_sdate=" + sdate;
        url += "&sc_edate=" + edate;
        url += "&wh_seq=" + wh_seq;
        url += "&keyword=" + encodeURIComponent(keyword);

        location.href = url;
    }
</script>
@endsection
