@extends('admin.layouts.admin')

@section('content')
<div class="page-header">
    <h3 class="page-title">월별 매입 분석</h3>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.scm_analysis.monthly') }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <select name="year" class="form-control">
                    @for($i = date('Y'); $i >= date('Y')-5; $i--)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}년</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">검색</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover text-center">
            <thead class="thead-light">
                <tr>
                    <th>월</th>
                    <th>입고 건수</th>
                    <th>입고 품목수 (구현필요)</th>
                    <th>공급가액</th>
                    <th>세액</th>
                    <th>합계금액</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalCnt = 0; $totalAmt = 0; $totalTax = 0; 
                @endphp
                @foreach ($stats as $month => $item)
                <tr>
                    <td class="font-weight-bold">{{ $item['month'] }}월</td>
                    <td>{{ number_format($item['cnt']) }}</td>
                    <td>-</td>
                    <td>{{ number_format($item['total_amt']) }}</td>
                    <td>{{ number_format($item['total_tax']) }}</td>
                    <td class="font-weight-bold">{{ number_format($item['total_amt'] + $item['total_tax']) }}</td>
                </tr>
                @php
                    $totalCnt += $item['cnt'];
                    $totalAmt += $item['total_amt'];
                    $totalTax += $item['total_tax'];
                @endphp
                @endforeach
            </tbody>
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <td>합계</td>
                    <td>{{ number_format($totalCnt) }}</td>
                    <td>-</td>
                    <td>{{ number_format($totalAmt) }}</td>
                    <td>{{ number_format($totalTax) }}</td>
                    <td class="text-primary">{{ number_format($totalAmt + $totalTax) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
