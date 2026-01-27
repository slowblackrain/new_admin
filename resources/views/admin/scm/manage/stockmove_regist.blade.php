@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">재고이동 등록</h1>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">이동 정보 입력</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.scm_manage.stockmove_save') }}" method="POST">
                        @csrf
                        
                        <!-- Warehouse Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">출고 창고 (보내는 곳)</label>
                                <select name="out_wh_seq" class="form-select" required>
                                    <option value="">선택하세요</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->wh_seq }}">{{ $wh->wh_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">입고 창고 (받는 곳)</label>
                                <select name="in_wh_seq" class="form-select" required>
                                    <option value="">선택하세요</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->wh_seq }}">{{ $wh->wh_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">관리자 메모</label>
                            <input type="text" name="admin_memo" class="form-control">
                        </div>

                        <!-- Goods Selection (Simplified for MVP) -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5>이동 상품 선택</h5>
                                <div class="input-group mb-3">
                                    <input type="text" id="goods_search_keyword" class="form-control" placeholder="상품명 또는 코드 검색 (Live DB)">
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchGoods()">검색</button>
                                </div>
                                
                                <div id="search_results" class="list-group mb-3" style="max-height: 200px; overflow-y: auto;">
                                    <!-- AJAX Results -->
                                </div>
                            </div>
                        </div>

                        <!-- Selected Goods List -->
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>상품명 (코드)</th>
                                    <th width="150">이동 수량</th>
                                    <th width="100">삭제</th>
                                </tr>
                            </thead>
                            <tbody id="selected_goods_list">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.scm_manage.stockmove') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">이동 저장</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple Goods Search Script (Needs Route)
function searchGoods() {
    const keyword = document.getElementById('goods_search_keyword').value;
    if (!keyword) return;

    // Use existing Goods Search API or create a temporary one for SCM
    // For now, assuming we might need a dedicated API returning JSON from Live DB
    fetch(`{{ route('admin.goods.search_json') }}?keyword=${keyword}&connection=live`) 
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('search_results');
            list.innerHTML = '';
            data.forEach(item => {
                const btn = document.createElement('button');
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = `[${item.goods_code}] ${item.goods_name}`;
                btn.type = 'button';
                btn.onclick = () => addGoods(item);
                list.appendChild(btn);
            });
        })
        .catch(err => console.error(err));
}

function addGoods(item) {
    const tbody = document.getElementById('selected_goods_list');
    
    // Check duplicate
    if (document.getElementById(`row_${item.goods_seq}`)) {
        alert('이미 추가된 상품입니다.');
        return;
    }

    const tr = document.createElement('tr');
    tr.id = `row_${item.goods_seq}`;
    tr.innerHTML = `
        <td>
            ${item.goods_name} <br> <small class="text-muted">${item.goods_code}</small>
        </td>
        <td>
            <input type="number" name="stock[${item.goods_seq}]" class="form-control" value="1" min="1">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">삭제</button>
        </td>
    `;
    tbody.appendChild(tr);
}
</script>
@endsection
