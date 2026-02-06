@extends('admin.layouts.admin')

@section('title', '상품 일괄 업데이트')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="page-title">상품 데이터 일괄 업데이트</h2>

        <!-- Mode Selection (Tabs) -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.goods.batch.index') }}">
                    <label class="font-weight-bold">수정 모드 선택:</label>
                    <select name="mode" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="price" {{ $mode == 'price' ? 'selected' : '' }}>금액/재고/상태 직접 업데이트</option>
                        <option value="category" {{ $mode == 'category' ? 'selected' : '' }}>카테고리 이동 (미구현)</option>
                        <!-- Add more modes here -->
                    </select>
                </form>
            </div>
        </div>

        <!-- Search Filter -->
        <div class="card mb-3">
            <div class="card-header">상품 검색</div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.goods.batch.index') }}">
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <input type="text" name="keyword" class="form-control" placeholder="상품명 검색" value="{{ request('keyword') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Form -->
        <form method="POST" action="{{ route('admin.goods.batch.update') }}">
            @csrf
            <input type="hidden" name="mode" value="{{ $mode }}">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>총 {{ $goodsList->total() }}개 상품</span>
                    <button type="submit" class="btn btn-danger">선택 상품 일괄 업데이트</button>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th width="30"><input type="checkbox" id="checkAll"></th>
                                <th>이미지</th>
                                <th>상품명/코드</th>
                                @if($mode == 'price')
                                    <th>소비자가/판매가/매입가</th>
                                    <th>재고(기본)</th>
                                    <th>상태(노출/판매)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($goodsList as $goods)
                                <tr>
                                    <td><input type="checkbox" name="goods_seq[]" value="{{ $goods->goods_seq }}" class="chk"></td>
                                    <td>
                                        <img src="{{ $goods->image[0] ?? '/images/no_image.png' }}" width="50" height="50">
                                    </td>
                                    <td class="text-left">
                                        {{ $goods->goods_name }}<br>
                                        <small class="text-muted">{{ $goods->goods_code }}</small>
                                    </td>
                                    @if($mode == 'price')
                                        <td>
                                            <input type="number" name="consumer_price[{{ $goods->goods_seq }}]" value="{{ $goods->consumer_price }}" class="form-control form-control-sm mb-1" placeholder="소비자가">
                                            <input type="number" name="price[{{ $goods->goods_seq }}]" value="{{ $goods->price }}" class="form-control form-control-sm mb-1" placeholder="판매가">
                                            <!-- B2B Rate or Supply Price logic here -->
                                        </td>
                                        <td>
                                            <input type="number" name="default_stock[{{ $goods->goods_seq }}]" value="{{ $goods->default_stock }}" class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <select name="goods_view[{{ $goods->goods_seq }}]" class="form-control form-control-sm mb-1">
                                                <option value="look" {{ $goods->goods_view == 'look' ? 'selected' : '' }}>노출</option>
                                                <option value="notLook" {{ $goods->goods_view == 'notLook' ? 'selected' : '' }}>미노출</option>
                                            </select>
                                            <select name="goods_status[{{ $goods->goods_seq }}]" class="form-control form-control-sm">
                                                <option value="normal" {{ $goods->goods_status == 'normal' ? 'selected' : '' }}>정상</option>
                                                <option value="runout" {{ $goods->goods_status == 'runout' ? 'selected' : '' }}>품절</option>
                                                <option value="stop" {{ $goods->goods_status == 'stop' ? 'selected' : '' }}>판매중지</option>
                                            </select>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="10">검색된 상품이 없습니다.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $goodsList->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </form>
    </div>
</div>

@section('custom_js')
<script>
    $('#checkAll').click(function(){
        $('.chk').prop('checked', this.checked);
    });
    
    @if(session('alert'))
        alert("{{ session('alert') }}");
    @endif
</script>
@endsection
@endsection
