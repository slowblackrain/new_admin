@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">재고자산명세서 (Inventory Asset Report)</h2>

        <div class="card shadow mb-4">
            <div class="card-header">
                <form action="{{ route('admin.scm_manage.inven') }}" method="GET" class="form-inline">
                    <label class="sr-only" for="sc_date">기준일</label>
                    <input type="date" class="form-control mb-2 mr-sm-2" id="sc_date" name="sc_date" value="{{ $filters['date'] }}">

                    <label class="sr-only" for="wh_seq">창고</label>
                    <select class="form-control mb-2 mr-sm-2" id="wh_seq" name="wh_seq">
                        <option value="">전체 창고</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->wh_seq }}" {{ ($filters['wh_seq'] == $wh->wh_seq) ? 'selected' : '' }}>
                                {{ $wh->wh_name }}
                            </option>
                        @endforeach
                    </select>

                    <label class="sr-only" for="keyword">키워드</label>
                    <input type="text" class="form-control mb-2 mr-sm-2" id="keyword" name="keyword" value="{{ $filters['keyword'] }}" placeholder="상품명/코드">

                    <button type="submit" class="btn btn-primary mb-2">검색</button>
                    <a href="{{ route('admin.scm_manage.inven') }}" class="btn btn-secondary mb-2 ml-2">초기화</a>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>기준일자</th>
                                <th>상품코드</th>
                                <th>상품명</th>
                                <th>창고</th>
                                @if($filters['wh_seq'])
                                    <th>재고수량 (WH)</th>
                                    <th>단가 (WH)</th>
                                    <th>평가금액 (WH)</th>
                                @else
                                    <th>재고수량 (Global)</th>
                                    <!-- <th>단가 (Avg)</th> -->
                                    <th>평가금액 (Global)</th>
                                @endif
                                <!-- <th>상세</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $item)
                                <tr>
                                    <td>{{ $item->ldg_date }}</td>
                                    <td>{{ $item->goods_code }}</td>
                                    <td onclick="sel_ledger('{{ $item->goods_seq }}', 'option', '0', '')" style="cursor:pointer; color:#007bff;">{{ $item->goods_name }}</td>
                                    <td>
                                        @if($item->wh_seq)
                                            <!-- Lookup WH Name or ID -->
                                            {{ $warehouses->where('wh_seq', $item->wh_seq)->first()->wh_name ?? $item->wh_seq }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    
                                    @if($filters['wh_seq'])
                                        <td class="text-right">{{ number_format($item->wh_cur_ea) }}</td>
                                        <td class="text-right">{{ number_format($item->wh_cur_supply_price, 2) }}</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($item->wh_cur_ea * $item->wh_cur_supply_price) }}
                                        </td>
                                    @else
                                        <!-- Global Mode -->
                                        <!-- Note: If querying ledger without WH filter, cur_ea is global stock for that snapshot -->
                                        <td class="text-right">{{ number_format($item->cur_ea) }}</td>
                                        <!-- <td class="text-right">{{ number_format($item->cur_supply_price, 2) }}</td> -->
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($item->cur_ea * $item->cur_supply_price) }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        데이터가 없습니다. (수불부 기록이 존재하지 않거나 조건에 맞는 데이터가 없음)
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($inventory->count() > 0)
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="4" class="text-right">합계 (현재 페이지)</th>
                                @if($filters['wh_seq'])
                                    <th class="text-right">{{ number_format($inventory->sum('wh_cur_ea')) }}</th>
                                    <th></th>
                                    <th class="text-right">{{ number_format($inventory->sum(function($i){ return $i->wh_cur_ea * $i->wh_cur_supply_price; })) }}</th>
                                @else
                                    <th class="text-right">{{ number_format($inventory->sum('cur_ea')) }}</th>
                                    <th class="text-right">{{ number_format($inventory->sum(function($i){ return $i->cur_ea * $i->cur_supply_price; })) }}</th>
                                @endif
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <div class="mt-4">
                    {{ $inventory->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    function sel_ledger(goods_seq, option_type, option_seq, price) {
        var dateInput = document.getElementById('sc_date');
        var whInput = document.getElementById('wh_seq');
        
        // Inventory Report is snapshot at a date. 
        // Ledger Detail is a RANGE.
        // Logic: Open ledger ending at this date? Or this month?
        // Default to current month ending at sc_date.
        var edate = dateInput ? dateInput.value : "{{ date('Y-m-d') }}";
        // Calculate sdate (e.g., 1st of that month)
        var d = new Date(edate);
        d.setDate(1);
        var year = d.getFullYear();
        var month = (d.getMonth() + 1).toString().padStart(2, '0');
        var sdate = year + '-' + month + '-01';

        var wh_seq = whInput ? whInput.value : "";
        
        var url = "{{ route('admin.scm_manage.ledger_detail') }}";
        url += "?goods_seq=" + goods_seq;
        url += "&option_type=" + option_type;
        url += "&option_seq=" + option_seq;
        url += "&out_supply_price=" + price;
        url += "&sc_sdate=" + sdate;
        url += "&sc_edate=" + edate;
        url += "&wh_seq=" + wh_seq;

        window.open(url, 'ledger_detail', 'width=1000,height=800,accumulate=yes,scrollbars=yes,resizable=yes');
    }
</script>
@endsection
