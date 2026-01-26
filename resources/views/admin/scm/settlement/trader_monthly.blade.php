@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">거래처 월별 정산 (Purchase Settlement)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="form-inline">
                        <label class="mr-2">귀속년월</label>
                        <select name="year" class="form-control mr-1">
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}년</option>
                            @endfor
                        </select>
                        <select name="month" class="form-control mr-2">
                            @foreach(range(1, 12) as $m)
                                @php $m_str = sprintf('%02d', $m); @endphp
                                <option value="{{ $m_str }}" {{ $month == $m_str ? 'selected' : '' }}>{{ $m }}월</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">조회</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>입고 완료(Step 11)</strong>된 발주서 기준 집계입니다.
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>거래처명</th>
                                <th>거래처 코드</th>
                                <th>발주/입고 건수</th>
                                <th>총 매입액 (KRW)</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $item)
                            <tr>
                                <td>{{ $item->trader_name }}</td>
                                <td>{{ $item->trader_code }}</td>
                                <td class="text-center">{{ number_format($item->offer_count) }}</td>
                                <td class="text-right"><strong>{{ number_format($item->total_purchase_amount) }}</strong></td>
                                <td class="text-center"><span class="badge badge-secondary">미정산</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="alert('정산서 상세 보기 기능 준비중')">상세</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    {{ $year }}년 {{ $month }}월의 매입 내역이 없습니다.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="2" class="text-center">합계</th>
                                <th class="text-center">{{ number_format($purchases->sum('offer_count')) }}</th>
                                <th class="text-right">{{ number_format($purchases->sum('total_purchase_amount')) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
