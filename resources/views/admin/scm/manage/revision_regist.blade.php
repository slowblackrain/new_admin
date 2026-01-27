@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">재고 조정 등록</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.scm_manage.revision.save') }}" method="POST">
                @csrf
                
                <!-- Master Info -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">기본 정보</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>대상 창고 <span class="text-danger">*</span></label>
                                    <select name="wh_seq" class="form-control" required>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->wh_seq }}" {{ $wh->wh_type == 1 ? 'selected' : '' }}>
                                                {{ $wh->wh_name }} ({{ $wh->wh_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>조정 유형 <span class="text-danger">*</span></label>
                                    <select name="revision_type" class="form-control" required>
                                        <option value="increase">증가 (입고/반품 등)</option>
                                        <option value="decrease">감소 (출고/망실 등)</option>
                                        <option value="set">재고 설정 (실사 반영)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>비고</label>
                                    <input type="text" name="admin_memo" class="form-control" placeholder="조정 사유 입력">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goods Selection -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">대상 상품 선택</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" id="goods_keyword" class="form-control float-right" placeholder="상품명/코드 검색 (Enter)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-default" onclick="searchGoods()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped" id="goodsTable">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>상품 정보</th>
                                    <th style="width: 150px">조정 수량</th>
                                    <th style="width: 50px"></th>
                                </tr>
                            </thead>
                            <tbody id="goodsList">
                                <!-- Dynamic Rows -->
                                <tr id="emptyRow">
                                    <td colspan="4" class="text-center py-4 text-muted">상단 검색을 통해 상품을 추가해주세요.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.scm_manage.revision') }}" class="btn btn-secondary">취소</a>
                        <button type="submit" class="btn btn-primary">조정 내역 저장</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Goods Search Modal/Script -->
<script>
function searchGoods() {
    let keyword = document.getElementById('goods_keyword').value;
    if(!keyword) { alert('검색어를 입력하세요.'); return; }

    // Use GoodsBatchController API
    fetch("{{ route('admin.goods.batch.search_json') }}?keyword=" + encodeURIComponent(keyword))
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                alert('검색된 상품이 없습니다.');
                return;
            }
            // For MVP, just pick first or show simple selector? 
            // Let's assume exact match or simple interaction: Add all found (up to limit) or prompt?
            // Better: Add them to the table if not exists.
            
            // Or show a simple modal (native)
            if(data.length > 1) {
                let msg = data.length + "건이 검색되었습니다. 목록에 추가하시겠습니까?\n(상위 20건)";
                if(!confirm(msg)) return;
            }
            
            data.forEach(item => {
                addGoodsRow(item);
            });
            document.getElementById('goods_keyword').value = '';
        })
        .catch(err => {
            console.error(err);
            alert('검색 중 오류가 발생했습니다.');
        });
}

function addGoodsRow(item) {
    let tbody = document.getElementById('goodsList');
    let emptyRow = document.getElementById('emptyRow');
    if(emptyRow) emptyRow.remove();

    // Check Duplicate
    if(document.querySelector(`input[name="stock[${item.goods_seq}]"]`)) {
        return; // Already added
    }

    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="align-middle text-center"><i class="fas fa-box"></i></td>
        <td class="align-middle">
            <strong>${item.goods_name}</strong><br>
            <small class="text-muted">${item.goods_code}</small>
        </td>
        <td class="align-middle">
            <input type="number" name="stock[${item.goods_seq}]" class="form-control" value="0" required>
        </td>
        <td class="align-middle">
            <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
}

// Enter key support
document.getElementById('goods_keyword').addEventListener("keyup", function(event) {
    if (event.key === "Enter") {
        searchGoods();
    }
});
</script>
@endsection
