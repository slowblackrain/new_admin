@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $order ? '발주 수정 (Edit Order)' : '발주 등록 (New Order)' }}</h3>
            </div>
            
            <form action="{{ route('admin.scm_order.store') }}" method="POST" id="orderForm">
                @csrf
                @if($order)
                    <input type="hidden" name="sorder_seq" value="{{ $order->sorder_seq }}">
                @endif
                
                <div class="card-body">
                    <!-- Header Info -->
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">발주번호 (Code)</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="sorder_code" value="{{ $order->sorder_code ?? 'Auto Generated' }}" readonly>
                        </div>
                        <label class="col-sm-2 col-form-label">등록일 (Date)</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" value="{{ $order->regist_date ?? date('Y-m-d') }}" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">매입처 (Trader) <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <select class="form-control" name="trader_seq" required {{ $order ? 'disabled' : '' }}>
                                <option value="">Select Trader</option>
                                @foreach($traders as $trader)
                                    <option value="{{ $trader->trader_seq }}" {{ ($order && $order->trader_seq == $trader->trader_seq) ? 'selected' : '' }}>
                                        {{ $trader->trader_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-sm-2 col-form-label">화폐단위 (Currency)</label>
                        <div class="col-sm-4">
                             <select class="form-control" name="supply_price_type">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr }}" {{ ($order && $order->supply_price_type == $curr) ? 'selected' : '' }}>{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Items -->
                    <h5 class="mt-4 mb-3 d-flex justify-content-between">
                        <span>발주 상품 (Order Items)</span>
                        <button type="button" class="btn btn-sm btn-primary" onclick="openGoodsSearch()">+ 상품추가 (Add Goods)</button>
                    </h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered text-center" id="itemTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>상품코드</th>
                                    <th>상품명/옵션</th>
                                    <th>매입가</th>
                                    <th>수량</th>
                                    <th>공급가액</th>
                                    <th>세액</th>
                                    <th>합계</th>
                                    <th>삭제</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($order && isset($order->items))
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->goods_code }}</td>
                                        <td class="text-left">
                                            {{ $item->goods_name }} <br>
                                            <small>{{ $item->option_name }}</small>
                                            <input type="hidden" name="items[{{ $loop->index }}][goods_seq]" value="{{ $item->goods_seq }}">
                                            <input type="hidden" name="items[{{ $loop->index }}][option_seq]" value="{{ $item->option_seq }}">
                                            <input type="hidden" name="items[{{ $loop->index }}][option_type]" value="{{ $item->option_type }}">
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm text-right price-input" name="items[{{ $loop->index }}][supply_price]" value="{{ round($item->supply_price) }}"></td>
                                        <td><input type="number" class="form-control form-control-sm text-right ea-input" name="items[{{ $loop->index }}][ea]" value="{{ $item->ea }}"></td>
                                        <td class="amt-display">{{ number_format($item->supply_price * $item->ea) }}</td>
                                        <td class="tax-display">{{ number_format($item->supply_tax ?? 0) }}</td>
                                        <td class="total-display">{{ number_format(($item->supply_price * $item->ea) + ($item->supply_tax ?? 0)) }}</td>
                                        <td><button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)">X</button></td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyRow">
                                        <td colspan="8" class="text-muted py-3">상품을 추가해주세요. (Please add goods)</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="3">Total</td>
                                    <td id="grandTotalEa" class="text-right">0</td>
                                    <td id="grandTotalAmt" class="text-right">0</td>
                                    <td id="grandTotalTax" class="text-right">0</td>
                                    <td id="grandTotalPrice" class="text-right">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.scm_order.index') }}" class="btn btn-secondary">목록 (List)</a>
                    <button type="submit" class="btn btn-primary">저장 (Save)</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    function openGoodsSearch() {
        // Placeholder for Goods Search Popup
        // In legacy: scm.select_goods_popup()
        // Here we might need to implement or use existing
        alert('상품 검색 팝업 (Goods Search Popup) - 구현 예정');
    }

    function removeRow(btn) {
        $(btn).closest('tr').remove();
        calcTotals();
    }

    function calcTotals() {
        // Logic to sum up EA, Price, Tax
        // Placeholder
    }
</script>
@endsection
