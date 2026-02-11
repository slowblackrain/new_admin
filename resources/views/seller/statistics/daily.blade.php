@extends('seller.layouts.app')

@section('title', '일별 매출통계')

@section('content')
<div class="container-fluid">
    <!-- Search Filter -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('seller.statistics.sales_daily') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="year" class="mr-2">연도</label>
                    <select name="year" id="year" class="form-control">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}년</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="month" class="mr-2">월</label>
                    <select name="month" id="month" class="form-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}월</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">검색</button>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $year }}년 {{ $month }}월 일별 매출현황</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>날짜</th>
                        <th>매출액</th>
                        <th>반품/환불</th>
                        <th>결제완료건수</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_sales = 0;
                        $total_refund = 0;
                        $total_count = 0;
                    @endphp
                    @foreach($statsData as $day => $data)
                    @php
                        $total_sales += $data['sales_price'];
                        $total_refund += $data['refund_price'];
                        $total_count += $data['count_sum'];
                    @endphp
                    <tr class="text-center">
                        <td>{{ $year }}-{{ sprintf('%02d', $month) }}-{{ sprintf('%02d', $day) }}</td>
                        <td class="text-right">{{ number_format($data['sales_price']) }}</td>
                        <td class="text-right text-danger">{{ number_format($data['refund_price']) }}</td>
                        <td class="text-right">{{ number_format($data['count_sum']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="text-center bg-light font-weight-bold">
                        <td>합계</td>
                        <td class="text-right">{{ number_format($total_sales) }}</td>
                        <td class="text-right text-danger">{{ number_format($total_refund) }}</td>
                        <td class="text-right">{{ number_format($total_count) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
