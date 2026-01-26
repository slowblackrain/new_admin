@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>상점 일반 정보</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">기본 정보 설정</h3>
                </div>
                <!-- form start -->
                <form action="{{ route('admin.setting.basic.save') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>상점명</label>
                            <input type="text" class="form-control" name="shop_name" value="{{ $config->shop_name ?? '' }}" placeholder="상점명을 입력하세요">
                        </div>
                        <div class="form-group">
                            <label>대표자명</label>
                            <input type="text" class="form-control" name="ceo_name" value="{{ $config->ceo_name ?? '' }}" placeholder="대표자명을 입력하세요">
                        </div>
                        <div class="form-group">
                            <label>사업자등록번호</label>
                            <input type="text" class="form-control" name="company_reg_no" value="{{ $config->company_reg_no ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>통신판매업신고번호</label>
                            <input type="text" class="form-control" name="network_reg_no" value="{{ $config->network_reg_no ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>대표전화</label>
                            <input type="text" class="form-control" name="phone" value="{{ $config->phone ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>이메일</label>
                            <input type="email" class="form-control" name="email" value="{{ $config->email ?? '' }}">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
