@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">쿠폰 {{ $coupon ? '수정' : '등록' }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.coupon.catalog') }}" class="btn btn-secondary">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form action="{{ route('admin.coupon.process') }}" method="POST">
                @csrf
                <input type="hidden" name="couponSeq" value="{{ $coupon ? $coupon->coupon_seq : '' }}">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">쿠폰 기본 정보</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm search-table">
                            <colgroup>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th>쿠폰명 <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="text" name="couponName" class="form-control form-control-sm" style="width: 400px;" required value="{{ $coupon ? $coupon->coupon_name : '' }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>쿠폰종류 <span class="text-danger">*</span></th>
                                    <td>
                                        <select name="couponType" class="form-control form-control-sm" style="width: 200px;">
                                            <option value="download" {{ ($coupon && $coupon->type == 'download') ? 'selected' : '' }}>상품/할인</option>
                                            <option value="shipping" {{ ($coupon && $coupon->type == 'shipping') ? 'selected' : '' }}>배송비 할인</option>
                                            <option value="member" {{ ($coupon && $coupon->type == 'member') ? 'selected' : '' }}>신규 가입 쿠폰</option>
                                            <option value="birthday" {{ ($coupon && $coupon->type == 'birthday') ? 'selected' : '' }}>생일자 쿠폰</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>할인혜택 <span class="text-danger">*</span></th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <select name="saleType" class="form-control form-control-sm mr-2" style="width: 150px;">
                                                <option value="percent" {{ ($coupon && $coupon->sale_type == 'percent') ? 'selected' : '' }}>% 할인</option>
                                                <option value="won" {{ ($coupon && $coupon->sale_type == 'won') ? 'selected' : '' }}>정액(원) 할인</option>
                                            </select>
                                            <input type="number" name="percentGoodsSale" class="form-control form-control-sm mr-2" style="width: 100px;" placeholder="%" value="{{ $coupon ? $coupon->percent_goods_sale : '' }}"> 
                                            <input type="number" name="wonGoodsSale" class="form-control form-control-sm mr-2" style="width: 120px;" placeholder="원" value="{{ $coupon ? $coupon->won_goods_sale : '' }}">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>상세설명</th>
                                    <td>
                                        <textarea name="couponDesc" class="form-control form-control-sm" rows="3">{{ $coupon ? $coupon->coupon_desc : '' }}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">저장</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
