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
                        정산 데이터는 매월 말일 기준으로 익월 초에 업데이트됩니다. (레거시 데이터 기반)
                    </div>

                    @if($atsData)
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-won-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">총 매출액</span>
                                        <span class="info-box-number">{{ number_format($atsData->sell_price ?? 0) }}원</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-undo"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">환불 금액</span>
                                        <span class="info-box-number">{{ number_format($atsData->refund_price ?? 0) }}원</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-hand-holding-usd"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">최종 정산금액</span>
                                        <span class="info-box-number">{{ number_format(($atsData->sell_price ?? 0) - ($atsData->refund_price ?? 0)) }}원</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detailed Table Placeholder --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>일자</th>
                                        <th>매출액</th>
                                        <th>환불액</th>
                                        <th>정산금액</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Daily breakdown logic is complex in legacy; showing simplified view or "See Detail" --}}
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">일별 상세 내역은 준비중입니다.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="p-5 text-center text-muted">
                            <h4>해당 월의 정산 데이터가 없습니다.</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
