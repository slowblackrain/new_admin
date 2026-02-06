@extends('admin.layouts.master')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        @if(isset($whs)) 입고 상세 ({{ $whs->whs_code }})
        @elseif(request('whs_type') == 'E') 비정규 입고 등록
        @else 입고 등록 (발주 기반)
        @endif
    </h3>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.scm_warehousing.store') }}" method="POST" id="warehousingForm">
            @csrf
            
            <input type="hidden" name="whs_type" value="{{ isset($whs) ? $whs->whs_type : (request('whs_type') == 'E' ? 'E' : 'S') }}">
            <input type="hidden" name="sorder_seq" value="{{ isset($order) ? $order->sorder_seq : (isset($whs) ? $whs->sorder_seq : '') }}">
            <input type="hidden" name="status" id="whs_status" value="{{ isset($whs) ? $whs->whs_status : '0' }}">

            <!-- Header Info -->
            <h5 class="border-bottom pb-2 mb-3">기본 정보</h5>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>거래처 <span class="text-danger">*</span></label>
                    <select name="trader_seq" class="form-control" {{ (isset($whs) || isset($order)) ? 'readonly' : '' }} required>
                        <option value="">선택하세요</option>
                        @foreach($traders as $trader)
                            <option value="{{ $trader->trader_seq }}" 
                                {{ (isset($whs) && $whs->trader_seq == $trader->trader_seq) || (isset($order) && $order->trader_seq == $trader->trader_seq) ? 'selected' : '' }}>
                                {{ $trader->trader_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>입고 창고 <span class="text-danger">*</span></label>
                    <select name="in_wh_seq" class="form-control" {{ (isset($whs) && $whs->whs_status == '1') ? 'disabled' : '' }}>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->wh_seq }}" {{ (isset($whs) && $whs->wh_seq == $wh->wh_seq) ? 'selected' : '' }}>
                                {{ $wh->wh_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>발주 번호</label>
                    <input type="text" class="form-control" value="{{ isset($order) ? $order->sorder_code : (isset($whs) ? $whs->sorder_code : '비정규 입고 (자동 생성)') }}" readonly>
                </div>
            </div>

            <!-- Items Info -->
            <h5 class="border-bottom pb-2 mb-3 mt-4 d-flex justify-content-between align-items-center">
                입고 상품 목록
                @if(!isset($whs) && request('whs_type') == 'E')
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGoodsSearch()">+ 상품 추가</button>
                @endif
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered text-center" id="goodsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>상품명/옵션</th>
                            @if(!isset($whs) && request('whs_type') != 'E') <th>발주수량</th> @endif
                            <th>입고수량</th>
                            <th>매입가</th>
                            <th>세액</th>
                            @if(!isset($whs) && request('whs_type') == 'E') <th>관리</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($order) && $order->items)
                            @foreach($order->items as $item)
                            <tr>
                                <td class="text-left">
                                    {{ $item->goods_name }} <br>
                                    <small class="text-muted">{{ $item->option_name }}</small>
                                    <input type="hidden" name="goods_seq[]" value="{{ $item->goods_seq }}">
                                    <input type="hidden" name="option_seq[]" value="{{ $item->option_seq }}">
                                    <input type="hidden" name="option_type[]" value="{{ $item->option_type }}">
                                </td>
                                <td>{{ number_format($item->ea) }}</td>
                                <td>
                                    <input type="number" name="ea[]" class="form-control form-control-sm text-right" 
                                           value="{{ $item->ea - $item->whs_ea }}" max="{{ $item->ea - $item->whs_ea }}">
                                    <input type="hidden" name="supply_price[]" value="{{ $item->supply_price }}">
                                    <input type="hidden" name="supply_tax[]" value="{{ $item->supply_tax }}">
                                </td>
                                <td>{{ number_format($item->supply_price) }}</td>
                                <td>{{ number_format($item->supply_tax) }}</td>
                            </tr>
                            @endforeach
                        @elseif(isset($whs) && $whs->items)
                            @foreach($whs->items as $item)
                            <tr>
                                <td class="text-left">
                                    {{ $item->goods_name }} <br>
                                    <small class="text-muted">{{ $item->option_name }}</small>
                                </td>
                                <td>{{ number_format($item->ea) }}</td>
                                <td>{{ number_format($item->supply_price) }}</td>
                                <td>-</td> <!-- Tax not stored in history details simply? -->
                            </tr>
                            @endforeach
                        @else
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-4 text-muted">입고할 상품을 추가해주세요.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">관리 메모</h5>
            <textarea name="admin_memo" class="form-control" rows="3">{{ isset($whs) ? $whs->admin_memo : '' }}</textarea>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.scm_warehousing.index') }}" class="btn btn-secondary">목록</a>
                @if(!isset($whs) || $whs->whs_status == '0')
                    <button type="button" class="btn btn-outline-dark" onclick="submitForm('0')">임시 저장</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm('1')">입고 완료</button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
function submitForm(status) {
    if (status === '1' && !confirm('입고 완료 처리하시겠습니까? 완료 후에는 수정할 수 없습니다.')) {
        return;
    }
    document.getElementById('whs_status').value = status;
    document.getElementById('warehousingForm').submit();
}

function openGoodsSearch() {
    // Placeholder for Goods Search Modal
    // In real implementation, this would open a popup to select goods
    // For now, let's just simulate adding a row for testing parity logic
    
    // Check if Trader is selected first
    const traderSeq = document.querySelector('select[name="trader_seq"]').value;
    if (!traderSeq) {
        alert('먼저 거래처를 선택해주세요.');
        return;
    }

    alert('상품 검색 팝업 (구현 예정) - 테스트용 더미 데이터가 추가됩니다.');
    
    // Remove empty row if exists
    const emptyRow = document.getElementById('emptyRow');
    if(emptyRow) emptyRow.remove();

    const tbody = document.getElementById('goodsTable').querySelector('tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-left">
            [테스트] 신규 추가 상품 <br>
            <small class="text-muted">기본 옵션</small>
            <input type="hidden" name="goods_seq[]" value="1">
            <input type="hidden" name="option_seq[]" value="1">
            <input type="hidden" name="option_type[]" value="option">
        </td>
        <td>
            <input type="number" name="ea[]" class="form-control form-control-sm text-right" value="1">
        </td>
        <td>
            <input type="number" name="supply_price[]" class="form-control form-control-sm text-right" value="10000">
        </td>
        <td>
            <input type="number" name="supply_tax[]" class="form-control form-control-sm text-right" value="1000">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">삭제</button>
        </td>
    `;
    tbody.appendChild(tr);
}
</script>
@endsection
