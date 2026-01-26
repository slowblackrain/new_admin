@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>재고이동 등록</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.scm_manage.stockmove.save') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">이동 정보</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>출고 창고 (보내는 곳) <span class="text-danger">*</span></label>
                                    <select name="out_wh_seq" class="form-control" required>
                                        <option value="">선택하세요</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->wh_seq }}">{{ $wh->wh_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>입고 창고 (받는 곳) <span class="text-danger">*</span></label>
                                    <select name="in_wh_seq" class="form-control" required>
                                        <option value="">선택하세요</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->wh_seq }}">{{ $wh->wh_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>관리자 메모</label>
                            <textarea name="admin_memo" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">이동 상품 선택</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-info" onclick="openGoodsSearch()">
                                <i class="fas fa-search"></i> 상품 검색
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped" id="move-table">
                            <thead>
                                <tr>
                                    <th>상품코드</th>
                                    <th>상품명</th>
                                    <th>수량</th>
                                    <th>삭제</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                         <div class="text-muted mb-2">※ 상품 검색 팝업에서 상품을 선택하면 여기에 추가됩니다.</div>
                         <div class="text-muted">※ (임시: 테스트를 위해 상품번호 직접 입력 가능)</div>
                    </div>
                </div>

                <!-- Manual Add for Testing (Temporary until Popup is ready) -->
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">수동 추가 (테스트용)</h3>
                        <div class="card-tools">
                             <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                         <div class="row">
                             <div class="col-4">
                                 <input type="number" id="manual_goods_seq" class="form-control" placeholder="상품번호 (Goods Seq)">
                             </div>
                             <div class="col-4">
                                 <input type="number" id="manual_qty" class="form-control" placeholder="수량" value="1">
                             </div>
                             <div class="col-4">
                                 <button type="button" class="btn btn-secondary btn-block" onclick="addManualRow()">추가</button>
                             </div>
                         </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('admin.scm_manage.stockmove') }}" class="btn btn-secondary">취소</a>
                        <button type="submit" class="btn btn-primary float-right">이동 처리 (저장)</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
function openGoodsSearch() {
    alert('상품 검색 팝업은 아직 구현 중입니다. 아래 수동 추가를 이용해주세요.');
}

function addManualRow() {
    var seq = document.getElementById('manual_goods_seq').value;
    var qty = document.getElementById('manual_qty').value;
    
    if(!seq || !qty) return;

    var html = `
        <tr>
            <td>(ID: ${seq})</td>
            <td>상품 번호 ${seq} (자동조회 미구현)</td>
            <td>
                <input type="number" name="stock[${seq}]" value="${qty}" class="form-control form-control-sm" style="width:100px;">
            </td>
            <td>
                <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove()">삭제</button>
            </td>
        </tr>
    `;
    
    document.querySelector('#move-table tbody').insertAdjacentHTML('beforeend', html);
    
    document.getElementById('manual_goods_seq').value = '';
    document.getElementById('manual_qty').value = '1';
}
</script>
@endsection
