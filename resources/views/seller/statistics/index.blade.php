@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">상품별 통계</h1>
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
                    <form method="GET" class="form-inline">
                        <label class="mr-2">기간:</label>
                        <input type="date" name="start_date" class="form-control mr-2" value="{{ $startDate }}">
                        ~
                        <input type="date" name="end_date" class="form-control ml-2 mr-2" value="{{ $endDate }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">통계 목록</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>순위</th>
                                <th>상품명</th>
                                <th>수량</th>
                                <th>결제금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statistics as $index => $stat)
                                <tr>
                                    <td>{{ $statistics->firstItem() + $index }}</td>
                                    <td>{{ $stat->goods_name }}</td>
                                    <td>{{ number_format($stat->total_ea) }}</td>
                                    <td>{{ number_format($stat->total_price) }}원</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">데이터가 없습니다.</td>
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
