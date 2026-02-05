@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">재고 이동 (Stock Move)</h2>
        
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title">이동 정보 입력</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.scm_manage.stockmove.save') }}" method="POST">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="out_wh_seq">보내는 창고 (Source)</label>
                            <select class="form-control" id="out_wh_seq" name="out_wh_seq" @if($move) disabled @endif>
                                <option value="">선택하세요</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->wh_seq }}" {{ ($move && $move->out_wh_seq == $wh->wh_seq) ? 'selected' : '' }}>
                                        {{ $wh->wh_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="in_wh_seq">받는 창고 (Target)</label>
                            <select class="form-control" id="in_wh_seq" name="in_wh_seq" @if($move) disabled @endif>
                                <option value="">선택하세요</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->wh_seq }}" {{ ($move && $move->in_wh_seq == $wh->wh_seq) ? 'selected' : '' }}>
                                        {{ $wh->wh_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_memo">메모</label>
                        <textarea class="form-control" id="admin_memo" name="admin_memo" rows="3" @if($move) readonly @endif>{{ $move->admin_memo ?? '' }}</textarea>
                    </div>

                    @if(!$move)
                    <div class="form-group">
                        <button type="button" class="btn btn-outline-primary" onclick="window.open('/admin/goods/search?callback=addGoods', 'goods_search', 'width=800,height=600')">
                            + 상품 추가 (Legacy Search Popup)
                        </button>
                    </div>
                    @endif

                    <table class="table table-bordered table-hover" id="goodsTable">
                        <thead class="thead-light">
                            <tr>
                                <th>상품코드</th>
                                <th>상품명</th>
                                <th>옵션</th>
                                <th width="150">이동 수량</th>
                                <th width="100">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic Rows -->
                            @if($move)
                                <!-- TODO: Loop through details if loaded -->
                                <tr><td colspan="5">상세 내역 로딩 구현 필요</td></tr>
                            @endif
                            <!-- Manual Add Row for Testing -->
                            <tr>
                                <td colspan="5" class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTestRow()">[DEBUG] 테스트 상품 추가</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @if(!$move)
                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-success">이동 확정 (Execute Move)</button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let rowIdx = 0;
function addTestRow() {
    // Adds a dummy row for testing without search popup
    // Assumes goods_seq exists in DB (e.g. 99999 or random)
    let tbody = document.querySelector('#goodsTable tbody');
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="goods_code[]" value="TEST_CODE" class="form-control" readonly>
            <input type="hidden" name="goods_seq[]" value="99999"></td>
        <td><input type="text" name="goods_name[]" value="테스트 이동 상품" class="form-control" readonly></td>
        <td><input type="text" name="option_name[]" value="기본옵션" class="form-control" readonly>
             <input type="hidden" name="option_seq[]" value="0"></td>
        <td><input type="number" name="ea[]" value="10" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">삭제</button></td>
    `;
    // Insert before the last row (debug button)
    tbody.insertBefore(tr, tbody.lastElementChild);
}
</script>
@endsection
