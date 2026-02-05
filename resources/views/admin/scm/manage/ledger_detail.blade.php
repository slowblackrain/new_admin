@extends('admin.layouts.admin') 

@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; shadow: none !important; }
        .card-header, .card-footer { display: none !important; }
        body { background: white; }
    }
    .top-toolbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: white;
        padding: 10px;
        border-bottom: 1px solid #ddd;
        z-index: 1000;
        text-align: right;
    }
    .content-wrapper {
        margin-top: 60px;
        padding: 20px;
    }
    .ledger-table th { background-color: #f8f9fa; }
    .sub-title { background-color: #FDEADA; font-weight: bold; }
</style>

<div class="top-toolbar no-print">
    <button onclick="window.print()" class="btn btn-primary btn-lg px-5">인쇄</button>
    <button onclick="window.close()" class="btn btn-secondary btn-lg px-5 ml-2">닫기</button>
</div>

<div class="container-fluid content-wrapper">
    <div class="text-center mb-4">
        <h2>재고수불부 (Stock Ledger)</h2>
    </div>

    <div class="row mb-3">
        <div class="col-6">
            <h5 class="font-weight-bold">[{{ $goods_info->goods_code }}] {{ $goods_info->goods_name }}</h5>
            <span class="badge badge-info p-2">{{ $goods_info->option_name }}</span>
            <span class="ml-2 text-warning font-weight-bold">
                @if($filters['wh_seq'])
                    {{ $warehouses->where('wh_seq', $filters['wh_seq'])->first()->wh_name ?? 'Unknown WH' }}
                @else
                    전체창고 (All Warehouses)
                @endif
            </span>
        </div>
        <div class="col-6 text-right">
            <h5>{{ $filters['start_date'] }} ~ {{ $filters['end_date'] }}</h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center ledger-table">
            <thead>
                <tr>
                    <th rowspan="2" class="align-middle">일자</th>
                    <th rowspan="2" class="align-middle">구분</th>
                    <th rowspan="2" class="align-middle">적요</th>
                    <th colspan="3">당기(월)입고</th>
                    <th colspan="3">당기(월)출고</th>
                    <th colspan="3">당기(월)재고</th>
                </tr>
                <tr>
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                </tr>
            </thead>
            <tbody>
                <!-- Opening Balance -->
                <tr class="sub-title">
                    <td colspan="7">전기(월)이월 (Opening)</td>
                    <td class="text-right">{{ number_format($pre_stock) }}</td>
                    <td class="text-right">{{ number_format($pre_stock > 0 ? ($history->first()->supply_price ?? 0) : 0) }}</td>
                    <td class="text-right">{{ number_format($pre_stock * ($history->first()->supply_price ?? 0)) }}</td>
                </tr>

                @forelse($history as $item)
                <tr>
                    <td>{{ $item->date }}</td>
                    <td>{{ $item->type }}</td>
                    <td class="text-left">{{ $item->memo }}</td>
                    
                    <!-- In -->
                    <td class="text-right">{{ $item->in_qty > 0 ? number_format($item->in_qty) : '-' }}</td>
                    <td class="text-right">{{ $item->in_qty > 0 ? number_format($item->supply_price) : '-' }}</td>
                    <td class="text-right">{{ $item->in_qty > 0 ? number_format($item->in_qty * $item->supply_price) : '-' }}</td>

                    <!-- Out -->
                    <td class="text-right">{{ $item->out_qty > 0 ? number_format($item->out_qty) : '-' }}</td>
                    <td class="text-right">{{ $item->out_qty > 0 ? number_format($item->supply_price) : '-' }}</td>
                    <td class="text-right">{{ $item->out_qty > 0 ? number_format($item->out_qty * $item->supply_price) : '-' }}</td>

                    <!-- Balance -->
                    <td class="text-right font-weight-bold">{{ number_format($item->current_stock) }}</td>
                    <td class="text-right">{{ number_format($item->supply_price) }}</td>
                    <td class="text-right">{{ number_format($item->current_stock * $item->supply_price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="py-5 text-center text-muted">기간 내 거래 내역이 없습니다.</td>
                </tr>
                @endforelse

                <!-- Closing Balance -->
                <tr class="sub-title" style="background-color: #eee;">
                    <td colspan="3">합 계 (Total)</td>
                    
                    <!-- Sum In -->
                    <td class="text-right">{{ number_format($history->sum('in_qty')) }}</td>
                    <td>-</td>
                    <td class="text-right">{{ number_format($history->sum(function($i){ return $i->in_qty * $i->supply_price; })) }}</td>

                    <!-- Sum Out -->
                    <td class="text-right">{{ number_format($history->sum('out_qty')) }}</td>
                    <td>-</td>
                    <td class="text-right">{{ number_format($history->sum(function($i){ return $i->out_qty * $i->supply_price; })) }}</td>

                    <!-- Final Balance -->
                    <td class="text-right">{{ number_format($cur_stock) }}</td>
                    <td>-</td>
                    <td class="text-right">{{ number_format($cur_stock * ($history->last()->supply_price ?? 0)) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
