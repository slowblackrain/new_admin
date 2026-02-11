@extends('seller.layouts.app')

@section('title', '상품별 판매통계')

@section('content')
<div class="container-fluid">
    <!-- Search Filter -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('seller.statistics.goods') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="sdate" class="mr-2">기간</label>
                    <input type="date" name="sdate" id="sdate" class="form-control" value="{{ $sdate }}">
                    <span class="mx-2">~</span>
                    <input type="date" name="edate" id="edate" class="form-control" value="{{ $edate }}">
                </div>
                <button type="submit" class="btn btn-primary">검색</button>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">상품별 판매 내역</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>순위</th>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>판매수량</th>
                        <th>총 판매금액</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsStats as $index => $item)
                    <tr class="text-center">
                        <td>{{ $goodsStats->firstItem() + $index }}</td>
                        <td>
                            @if($item->image)
                            <img src="/data/goods/{{ $item->image }}" alt="img" width="50" onerror="this.src='/images/no_img.gif'">
                            @else
                            <span class="text-muted">No Image</span>
                            @endif
                        </td>
                        <td class="text-left">{{ $item->goods_name }}</td>
                        <td class="text-right">{{ number_format($item->total_ea) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($item->total_price) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">데이터가 없습니다.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $goodsStats->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
