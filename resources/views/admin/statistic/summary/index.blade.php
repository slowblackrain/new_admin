@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>매출/주문 요약 통계</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Search -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.statistic_summary.index') }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-2">기간 조회</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            <span class="mx-2">~</span>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <!-- Chart/Table -->
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">일별 매출 현황</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                        <tr>
                            <th>날짜</th>
                            <th>주문 건수</th>
                            <th>매출액</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sales as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td>{{ number_format($row->order_count) }} 건</td>
                            <td>{{ number_format($row->total_sales) }} 원</td>
                        </tr>
                        @endforeach
                        @if($sales->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center">데이터가 없습니다.</td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
