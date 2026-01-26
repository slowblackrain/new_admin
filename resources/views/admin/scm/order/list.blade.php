@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">발주 현황</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">발주 내역</h3>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="step" class="form-control">
                                    <option value="">-- 상태 전체 --</option>
                                    <option value="1" {{ request('step') == '1' ? 'selected' : '' }}>발주(주문)</option>
                                    <option value="11" {{ request('step') == '11' ? 'selected' : '' }}>입고완료</option>
                                </select>
                            </div>
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
                                <input type="text" name="keyword" class="form-control" placeholder="상품명/코드 검색" value="{{ request('keyword') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </form>

                    <!-- Actions Form -->
                    <form action="{{ route('admin.scm_order.update_status') }}" method="POST">
                        @csrf
                        <div class="mb-2 text-right">
                            <button type="submit" name="action" value="stock" class="btn btn-success" onclick="return confirm('선택한 발주 건을 입고 처리하시겠습니까? 재고가 증가합니다.');">
                                <i class="fas fa-box-open"></i> 선택 입고 처리
                            </button>
                        </div>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px"><input type="checkbox" id="check_all"></th>
                                    <th>발주번호</th>
                                    <th>발주일자</th>
                                    <th>거래처</th>
                                    <th>상품명 (코드)</th>
                                    <th>발주수량</th>
                                    <th>상태</th>
                                    <th>입고일자</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offers as $offer)
                                <tr>
                                    <td>
                                        <!-- Only allow action for 'Ordered' status -->
                                        @if($offer->step == 1)
                                        <input type="checkbox" name="chk[]" value="{{ $offer->sno }}" class="item_check">
                                        @endif
                                    </td>
                                    <td>{{ $offer->sno }}</td>
                                    <td>{{ $offer->regist_date }}</td>
                                    <td>{{ $offer->trader_name }}</td>
                                    <td>
                                        {{ $offer->goods_name }} <br>
                                        <small class="text-muted">{{ $offer->goods_code }}</small>
                                    </td>
                                    <td class="font-weight-bold">{{ number_format($offer->ord_stock) }}</td>
                                    <td>
                                        @if($offer->step == 1)
                                            <span class="badge badge-warning">발주중</span>
                                        @elseif($offer->step == 11)
                                            <span class="badge badge-success">입고완료</span>
                                        @else
                                            <span class="badge badge-secondary">기타({{ $offer->step }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $offer->stock_date != '0000-00-00 00:00:00' ? $offer->stock_date : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            {{ $offers->links() }}
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
    });
});
</script>
@endsection
