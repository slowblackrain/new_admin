@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">자동 발주 (Auto Order)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">발주 필요 상품 목록</h3>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="trader_seq" class="form-control">
                                    <option value="">-- 거래처 선택 --</option>
                                    @foreach($traders as $t)
                                    <option value="{{ $t->trader_seq }}" {{ request('trader_seq') == $t->trader_seq ? 'selected' : '' }}>
                                        {{ $t->trader_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="keyword" class="form-control" placeholder="상품명 검색" value="{{ request('keyword') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </form>

                    <!-- Order Creation Form -->
                    <form action="{{ route('admin.scm_order.create') }}" method="POST">
                        @csrf
                        <div class="mb-2 text-right">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('선택한 상품을 발주하시겠습니까?');">
                                <i class="fas fa-check"></i> 선택 상품 발주 생성
                            </button>
                        </div>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px"><input type="checkbox" id="check_all"></th>
                                    <th>상품코드</th>
                                    <th>상품명</th>
                                    <th>거래처</th>
                                    <th>주문요구량(A)</th>
                                    <th>현재재고(B)</th>
                                    <th>필요수량(A-B)</th>
                                    <th>발주수량</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                @php
                                    $net_need = max(0, $item->required_qty - $item->current_stock);
                                    $row_class = $net_need > 0 ? 'bg-warning' : ''; 
                                @endphp
                                <tr class="{{ $net_need > 0 ? 'table-warning' : '' }}">
                                    <td>
                                        @if($net_need > 0)
                                        <input type="checkbox" class="item_check">
                                        @endif
                                    </td>
                                    <td>{{ $item->goods_code }}</td>
                                    <td>{{ $item->goods_name }}</td>
                                    <td>{{ $item->trader_name ?? '미지정' }}</td>
                                    <td>{{ number_format($item->required_qty) }}</td>
                                    <td>{{ number_format($item->current_stock) }}</td>
                                    <td class="font-weight-bold text-danger">{{ number_format($net_need) }}</td>
                                    <td>
                                        @if($net_need > 0)
                                        <input type="number" 
                                               name="orders[{{ $item->goods_seq }}]" 
                                               class="form-control form-control-sm order_qty" 
                                               style="width: 80px" 
                                               value="{{ $net_need }}"
                                               disabled> 
                                        <!-- Disabled initially, enabled when checked -->
                                        @else
                                        <span class="text-success">주충족</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            {{ $items->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById('check_all').addEventListener('change', function() {
    let checked = this.checked;
    document.querySelectorAll('.item_check').forEach(function(el) {
        el.checked = checked;
        toggleInput(el);
    });
});

document.querySelectorAll('.item_check').forEach(function(el) {
    el.addEventListener('change', function() {
        toggleInput(this);
    });
});

function toggleInput(checkbox) {
    let row = checkbox.closest('tr');
    let input = row.querySelector('.order_qty');
    if(input) {
        input.disabled = !checkbox.checked;
        if(checkbox.checked) {
             // If needed, sync value to checkbox 'value' attribute isn't strictly necessary if using input name
             // But here we used the same name for checkbox and input, which causes conflict in Laravel Request.
             // BETTER APPROACH: Checkbox enables input, Input has the name. Checkbox has no name or different name.
        }
    }
}
</script>

<!-- Fix Logic in View: Checkbox shouldn't have same name as Input. -->
<!-- Adjusting View Logic below via second write or just inline correction -->
@endsection
