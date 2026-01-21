@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">상품 매출 통계</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">검색 조건</h3>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <label>기간</label>
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                    <div class="input-group-append"><span class="input-group-text">~</span></div>
                                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>상품명</label>
                                <div class="input-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="상품명 검색" value="{{ request('keyword') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">검색</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>순위</th>
                                <th>상품명</th>
                                <th>주문건수</th>
                                <th>판매수량</th>
                                <th>판매금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statistics as $index => $stat)
                                <tr>
                                    <td>{{ $statistics->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($stat->image)
                                                <img src="/data/goods/{{ $stat->image }}" alt="img" class="img-thumbnail mr-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                            <span>{{ $stat->goods_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($stat->order_count) }}건</td>
                                    <td>{{ number_format($stat->total_ea) }}개</td>
                                    <td>{{ number_format($stat->total_price) }}원</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">검색된 통계 데이터가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $statistics->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>

        </div>
    </div>
@endsection
