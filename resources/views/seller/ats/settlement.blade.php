@extends('seller.layouts.app')

@section('title', '상품투자 정산확인')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form method="GET" action="{{ route('seller.ats.settlement') }}" class="form-inline">
                        <label class="mr-2">정산년월:</label>
                        <select name="year" class="form-control mr-2">
                            @for($y = date('Y'); $y >= 2019; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}년</option>
                            @endfor
                        </select>
                        <select name="month" class="form-control mr-2">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $month == $m ? 'selected' : '' }}>{{ sprintf('%02d', $m) }}월</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> 안내</h5>
                        정산 데이터는 매월 말일 기준으로 익월 초에 업데이트됩니다. 아래 데이터는 실시간 집계이므로 최종 정산금액과 다를 수 있습니다.
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-won-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">총 매출액</span>
                                    <span class="info-box-number">{{ number_format($totals['sell']) }}원</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-undo"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">환불 금액</span>
                                    <span class="info-box-number">{{ number_format($totals['refund']) }}원</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">발주(투자) 금액</span>
                                    <span class="info-box-number">{{ number_format($totals['offer']) }}원</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-hand-holding-usd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">정산 예상금액</span>
                                    <span class="info-box-number">{{ number_format($totals['sell'] - $totals['refund']) }}원</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Detailed Table --}}
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered text-center table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>일자</th>
                                    <th>매출액</th>
                                    <th>환불액</th>
                                    <th>발주(투자)액</th>
                                    <th>캐시적립</th>
                                    <th>비고</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statsData as $day => $data)
                                    <tr>
                                        <td>{{ $data['day'] }}일</td>
                                        <td class="text-right">{{ number_format($data['settleprice_sum']) }}</td>
                                        <td class="text-right text-danger">{{ number_format($data['refund_price_sum']) }}</td>
                                        <td class="text-right">{{ number_format($data['offer_price']) }}</td>
                                        <td class="text-right">{{ number_format($data['day_cash']) }}</td>
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">해당 월의 데이터가 없습니다.</td>
                                    </tr>
                                @endforelse
                                <tr class="bg-light font-weight-bold">
                                    <td>합계</td>
                                    <td class="text-right">{{ number_format($totals['sell']) }}</td>
                                    <td class="text-right text-danger">{{ number_format($totals['refund']) }}</td>
                                    <td class="text-right">{{ number_format($totals['offer']) }}</td>
                                    <td class="text-right">{{ number_format($totals['cash']) }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
