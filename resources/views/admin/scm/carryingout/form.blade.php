@extends('admin.layouts.master')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        @if(isset($cro)) 반출 상세 ({{ $cro->cro_code }})
        @else 반출 등록 (폐기/반품)
        @endif
    </h3>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.scm_carryingout.store') }}" method="POST" id="carryingOutForm">
            @csrf
            
            <input type="hidden" name="cro_seq" value="{{ isset($cro) ? $cro->cro_seq : '' }}">
            <input type="hidden" name="status" id="cro_status" value="{{ isset($cro) ? $cro->cro_status : '0' }}">
            <input type="hidden" name="cro_type" value="E"> <!-- Default to Exception/Manual -->

            <!-- Header Info -->
            <h5 class="border-bottom pb-2 mb-3">기본 정보</h5>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>거래처 <span class="text-danger">*</span></label>
                    <select name="trader_seq" class="form-control" {{ isset($cro) ? 'disabled' : '' }} required>
                        <option value="">선택하세요</option>
                        @foreach($traders as $trader)
                            <option value="{{ $trader->trader_seq }}" {{ (isset($cro) && $cro->trader_seq == $trader->trader_seq) ? 'selected' : '' }}>
                                {{ $trader->trader_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>반출 창고 <span class="text-danger">*</span></label>
                    <select name="in_wh_seq" class="form-control" {{ (isset($cro) && $cro->cro_status == '1') ? 'disabled' : '' }}>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->wh_seq }}" {{ (isset($cro) && $cro->wh_seq == $wh->wh_seq) ? 'selected' : '' }}>
                                {{ $wh->wh_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>비고</label>
                    <input type="text" class="form-control" value="수동 반출 (재고 차감)" readonly>
                </div>
            </div>

            <!-- Items Info -->
            <h5 class="border-bottom pb-2 mb-3 mt-4 d-flex justify-content-between align-items-center">
                반출 상품 목록
                @if(!isset($cro))
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="openGoodsSearch()">+ 상품 추가</button>
                @endif
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered text-center" id="goodsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>상품명/옵션</th>
                            <th>반출수량</th>
                            <th>공급가</th>
                            <th>세액</th>
                            @if(!isset($cro)) <th>관리</th> @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($cro) && $cro->items)
                            @foreach($cro->items as $item)
                            <tr>
                                <td class="text-left">
                                    {{ $item->goods_name }} <br>
                                    <small class="text-muted">{{ $item->option_name }}</small>
                                </td>
                                <td>{{ number_format($item->ea) }}</td>
                                <td>{{ number_format($item->supply_price) }}</td>
                                <td>{{ number_format($item->supply_tax) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-4 text-muted">반출할 상품을 추가해주세요.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">관리 메모</h5>
            <textarea name="admin_memo" class="form-control" rows="3">{{ isset($cro) ? $cro->admin_memo : '' }}</textarea>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.scm_carryingout.index') }}" class="btn btn-secondary">목록</a>
                @if(!isset($cro) || $cro->cro_status == '0')
                    <button type="button" class="btn btn-outline-dark" onclick="submitForm('0')">임시 저장</button>
                    <button type="button" class="btn btn-danger" onclick="submitForm('1')">반출 완료 (재고 차감)</button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
function submitForm(status) {
    if (status === '1' && !confirm('반출 완료 처리하시겠습니까? 완료 후에는 재고가 차감되며 수정할 수 없습니다.')) {
        return;
    }
    document.getElementById('cro_status').value = status;
    document.getElementById('carryingOutForm').submit();
}

function openGoodsSearch() {
    // Placeholder for Goods Search Modal
    // Using same dummy logic as Warehousing for parity verification
    
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
            [테스트] 반출 상품 <br>
            <small class="text-muted">기본 옵션</small>
            <input type="hidden" name="goods_seq[]" value="56838"> <!-- VerifyWarehousing Goods -->
            <input type="hidden" name="option_seq[]" value="122944"> <!-- VerifyWarehousing Option -->
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
