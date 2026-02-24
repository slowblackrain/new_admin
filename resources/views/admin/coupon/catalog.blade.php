@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">할인 쿠폰 관리</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.coupon.regist') }}" class="btn btn-primary">쿠폰 등록</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Search Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">쿠폰 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.coupon.catalog') }}" method="GET">
                        <table class="table table-bordered table-sm search-table">
                            <colgroup>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th>검색어</th>
                                    <td colspan="3">
                                        <input type="text" name="search_text" class="form-control form-control-sm d-inline-block" style="width: 300px;" placeholder="쿠폰명 입력" value="{{ request('search_text') }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>등록일</th>
                                    <td colspan="3">
                                        <div class="d-flex align-items-center">
                                            <input type="date" name="sdate" class="form-control form-control-sm" style="width: 150px;" value="{{ request('sdate') }}">
                                            <span class="mx-2">~</span>
                                            <input type="date" name="edate" class="form-control form-control-sm" style="width: 150px;" value="{{ request('edate') }}">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-dark btn-lg px-5">검색</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Table -->
            <div class="card">
                <div class="card-header">
                     <h3 class="card-title">총 {{ number_format($coupons->total()) }}건</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover text-sm table-striped">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="50">번호</th>
                                <th width="150">쿠폰종류</th>
                                <th>쿠폰명</th>
                                <th width="150">할인혜택</th>
                                <th width="200">유효기간</th>
                                <th width="80">발급</th>
                                <th width="80">중지</th>
                                <th width="80">수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $index => $coupon)
                                <tr>
                                    <td class="text-center align-middle">{{ $coupons->firstItem() + $index }}</td>
                                    <td class="text-center align-middle">{{ $coupon->type }}</td>
                                    <td class="align-middle font-weight-bold">{{ $coupon->coupon_name }}</td>
                                    <td class="text-center align-middle text-danger font-weight-bold">
                                        @if($coupon->sale_type == 'percent')
                                            {{ $coupon->percent_goods_sale }}% 할인
                                        @else
                                            {{ number_format($coupon->won_goods_sale) }}원 할인
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($coupon->issue_priod_type == 'date')
                                            {{ substr($coupon->issue_startdate, 0, 10) }} ~ {{ substr($coupon->issue_enddate, 0, 10) }}
                                        @elseif($coupon->issue_priod_type == 'day')
                                            발급일로부터 {{ $coupon->after_issue_day }}일
                                        @else
                                            해당 월 말일
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-xs btn-info">발급내역</button>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($coupon->issue_stop == '1')
                                            <span class="text-danger">발급중지</span>
                                        @else
                                            <span class="text-success">발급중</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.coupon.regist', ['no' => $coupon->coupon_seq]) }}" class="btn btn-xs btn-secondary">수정</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">등록된 쿠폰이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $coupons->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
