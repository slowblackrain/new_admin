@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">거래처 관리</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">거래처 목록</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.scm_basic.trader.form') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 신규 등록
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="keyword" class="form-control" placeholder="거래처명 검색" value="{{ request('keyword') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-default">검색</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>거래처명</th>
                                <th>그룹</th>
                                <th>유형</th>
                                <th>대표자</th>
                                <th>전화번호</th>
                                <th>상태</th>
                                <th>등록일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($traders as $trader)
                            <tr>
                                <td>{{ $trader->trader_seq }}</td>
                                <td>{{ $trader->trader_name }}</td>
                                <td>{{ $trader->trader_group }}</td>
                                <td>{{ $trader->business_type }}</td>
                                <td>{{ $trader->company_owner }}</td>
                                <td>{{ $trader->phone_number }}</td>
                                <td>{{ $trader->trader_use == 'Y' ? '사용' : '미사용' }}</td>
                                <td>{{ $trader->regist_date }}</td>
                                <td>
                                    <a href="{{ route('admin.scm_basic.trader.form', ['seq' => $trader->trader_seq]) }}" class="btn btn-info btn-xs">수정</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $traders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
